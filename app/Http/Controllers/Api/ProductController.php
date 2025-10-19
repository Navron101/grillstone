<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $locationId = (int) $request->query('location_id', 1);

        // Build a base query
        $q = Product::query()->orderBy('name');

        // Prefer dishes; if table has type column, filter by dish, else skip
        if (Schema::hasColumn('products', 'type')) {
            $q->where(function ($qq) {
                $qq->where('type', 'dish')
                   ->orWhereNull('type'); // tolerate older rows
            });
        }

        // Select flexible columns (fall back if some don't exist)
        $selects = ['id', 'name'];
        if (Schema::hasColumn('products', 'price_cents')) $selects[] = 'price_cents';
        if (Schema::hasColumn('products', 'price'))       $selects[] = 'price'; // old decimal
        if (Schema::hasColumn('products', 'category'))    $selects[] = 'category';
        if (Schema::hasColumn('products', 'image_url'))   $selects[] = 'image_url';
        if (Schema::hasColumn('products', 'description')) $selects[] = 'description';
        if (Schema::hasColumn('products', 'is_popular'))  $selects[] = 'is_popular';
        if (Schema::hasColumn('products', 'low_stock_threshold')) $selects[] = 'low_stock_threshold';

        $rows = $q->get($selects);

        // Get stock quantities for all products
        $productIds = $rows->pluck('id')->toArray();
        $stockMap = [];

        // Get ingredient stock levels
        if (!empty($productIds)) {
            $stockData = DB::table('inventory_lots')
                ->select('product_id', DB::raw('COALESCE(SUM(qty_on_hand),0) AS on_hand'))
                ->where('location_id', $locationId)
                ->whereIn('product_id', $productIds)
                ->groupBy('product_id')
                ->get();

            foreach ($stockData as $row) {
                $stockMap[$row->product_id] = (float) $row->on_hand;
            }
        }

        // Calculate dish stock based on ingredient availability
        foreach ($rows as $product) {
            if (!isset($stockMap[$product->id])) {
                // This is a dish - calculate how many we can make
                $dishStock = $this->calculateDishStock($product->id, $locationId);
                $stockMap[$product->id] = $dishStock;
            }
        }

        // Map to POS UI shape
        $out = $rows->map(function ($p) use ($stockMap) {
            // price: prefer cents, else decimal
            $price = 0.0;
            if (isset($p->price_cents) && $p->price_cents !== null) {
                $price = ((int)$p->price_cents) / 100;
            } elseif (isset($p->price) && $p->price !== null) {
                $price = (float)$p->price;
            }

            $onHand = $stockMap[$p->id] ?? 0;
            $threshold = isset($p->low_stock_threshold) ? (float)$p->low_stock_threshold : 5.0;

            return [
                'id'                   => (int) $p->id,
                'name'                 => (string) $p->name,
                'price'                => $price,
                'category'             => (string) ($p->category ?? 'Other'),
                'img'                  => $p->image_url ?? null,
                'description'          => (string) ($p->description ?? ''),
                'popular'              => (bool) ($p->is_popular ?? false),
                'on_hand'              => $onHand,
                'low_stock_threshold'  => $threshold,
                'is_low_stock'         => $onHand <= $threshold && $onHand > 0,
                'is_out_of_stock'      => $onHand <= 0,
            ];
        });

        return response()->json($out);
    }

    /**
     * Calculate how many of this dish can be made based on ingredient stock
     * Returns the minimum number based on all recipe components
     */
    private function calculateDishStock(int $dishProductId, int $locationId): float
    {
        // Get the default variant for this dish (or first variant if no default)
        $variant = DB::table('recipe_variants')
            ->where('product_id', $dishProductId)
            ->orderByDesc('is_default')
            ->first();

        if (!$variant) {
            // No recipe defined, can't calculate stock
            return 0;
        }

        // Get all recipe components (ingredients) for this variant
        $components = DB::table('recipe_components')
            ->where('variant_id', $variant->id)
            ->get();

        if ($components->isEmpty()) {
            // No ingredients defined, can't make any
            return 0;
        }

        $minDishesAvailable = PHP_FLOAT_MAX;

        foreach ($components as $component) {
            // Get stock for this ingredient
            $ingredientStock = DB::table('inventory_lots')
                ->where('product_id', $component->ingredient_product_id)
                ->where('location_id', $locationId)
                ->sum('qty_on_hand');

            $ingredientStock = (float) $ingredientStock;

            // How many dishes can we make with this ingredient?
            $qtyNeededPerDish = (float) $component->qty_per_unit;

            if ($qtyNeededPerDish <= 0) {
                continue; // Skip if qty is invalid
            }

            $dishesFromThisIngredient = floor($ingredientStock / $qtyNeededPerDish);

            // The limiting ingredient determines how many dishes we can make
            $minDishesAvailable = min($minDishesAvailable, $dishesFromThisIngredient);
        }

        // If no valid components were found, return 0
        return $minDishesAvailable === PHP_FLOAT_MAX ? 0 : $minDishesAvailable;
    }
}
