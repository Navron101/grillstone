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
        // Build a base query
        $q = Product::query()->orderBy('name');

        // Prefer dishes; if table has type column, filter by dish, else skip
        if (Schema::hasColumn('products', 'type')) {
            $q->where(function ($qq) {
                $qq->where('type', 'dish')
                   ->orWhereNull('type'); // tolerate older rows
            });
        }

        // Select flexible columns (fall back if some don’t exist)
        $selects = ['id', 'name'];
        if (Schema::hasColumn('products', 'price_cents')) $selects[] = 'price_cents';
        if (Schema::hasColumn('products', 'price'))       $selects[] = 'price'; // old decimal
        if (Schema::hasColumn('products', 'category'))    $selects[] = 'category';
        if (Schema::hasColumn('products', 'image_url'))   $selects[] = 'image_url';
        if (Schema::hasColumn('products', 'description')) $selects[] = 'description';
        if (Schema::hasColumn('products', 'is_popular'))  $selects[] = 'is_popular';

        $rows = $q->get($selects);

        // Map to POS UI shape
        $out = $rows->map(function ($p) {
            // price: prefer cents, else decimal
            $price = 0.0;
            if (isset($p->price_cents) && $p->price_cents !== null) {
                $price = ((int)$p->price_cents) / 100;
            } elseif (isset($p->price) && $p->price !== null) {
                $price = (float)$p->price;
            }

            return [
                'id'          => (int) $p->id,
                'name'        => (string) $p->name,
                'price'       => $price,
                'category'    => (string) ($p->category ?? 'Other'),
                'img'         => $p->image_url ?? null,
                'description' => (string) ($p->description ?? ''),
                'popular'     => (bool) ($p->is_popular ?? false),
            ];
        });

        return response()->json($out);
    }
}
