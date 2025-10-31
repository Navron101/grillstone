<?php

namespace App\Services\Sales;

use Illuminate\Support\Facades\DB;
use App\Models\Order;

class RecipeConsumption
{
    /**
     * For each order line (dish), find default variant and consume ingredients FIFO.
     * Returns total COGS (cents) for the order.
     */
    public function apply(Order $order, int $locationId = 1): int
    {
        $order->loadMissing('items');
        $totalCogs = 0;

        foreach ($order->items as $item) {
            // Check if this is a combo order item
            $meta = is_string($item->meta) ? json_decode($item->meta, true) : $item->meta;
            $comboId = $meta['combo_id'] ?? null;

            // Handle combos: consume combo items instead of dish variants
            if ($comboId) {
                $comboItems = DB::table('combo_items')->where('combo_id', $comboId)->get();

                foreach ($comboItems as $comboItem) {
                    $needQty = (float)$comboItem->quantity * (int)$item->qty;
                    if ($needQty <= 0) continue;

                    $totalCogs += $this->consumeIngredient(
                        $comboItem->product_id,
                        $needQty,
                        $locationId,
                        $order->id,
                        $comboId
                    );
                }
                continue; // Skip regular dish processing for combo items
            }

            // Regular dish processing (existing logic)
            $dishId = $item->product_id;
            $variant = DB::table('recipe_variants')
                ->where('product_id',$dishId)
                ->orderByDesc('is_default')->orderBy('id')
                ->first();
            if (!$variant) continue; // no recipe: skip

            $components = DB::table('recipe_components')->where('variant_id',$variant->id)->get();
            foreach ($components as $c) {
                $needQty = (float)$c->qty_per_unit * (int)$item->qty; // required ingredient qty
                if ($needQty <= 0) continue;

                // FIFO consume from inventory_lots for the ingredient at this location
                $lots = DB::table('inventory_lots')
                    ->where('product_id',$c->ingredient_product_id)
                    ->where('location_id',$locationId)
                    ->where('qty_on_hand','>',0)
                    ->orderBy('received_at')->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                foreach ($lots as $lot) {
                    if ($needQty <= 0) break;
                    $take = min($needQty, (float)$lot->qty_on_hand);
                    $needQty -= $take;

                    // cost = take * unit_cost
                    $totalCogs += (int) round($take * (int)$lot->unit_cost_cents);

                    DB::table('inventory_lots')->where('id',$lot->id)->update([
                        'qty_on_hand' => DB::raw('qty_on_hand - '.(float)$take),
                        'updated_at'=>now(),
                    ]);

                    if (DB::getSchemaBuilder()->hasTable('stock_movements')) {
                        DB::table('stock_movements')->insert([
                            'product_id' => $c->ingredient_product_id,
                            'location_id'=> $locationId,
                            'lot_id'     => $lot->id,
                            'direction'  => 'out',
                            'qty'        => $take,
                            'unit_cost_cents'=> $lot->unit_cost_cents,
                            'reason'     => 'sale',
                            'meta'       => json_encode(['order_id'=>$order->id, 'dish_id'=>$dishId]),
                            'created_at' => now(), 'updated_at'=>now(),
                        ]);
                    }
                }

                // If needQty > 0 here, we sold more than we have -> create negative inventory lot
                if ($needQty > 0) {
                    // Get last lot's cost for valuation, or use 0
                    $lastLot = DB::table('inventory_lots')
                        ->where('product_id',$c->ingredient_product_id)
                        ->where('location_id',$locationId)
                        ->orderByDesc('received_at')->orderByDesc('id')
                        ->first();

                    $unitCost = $lastLot ? (int)$lastLot->unit_cost_cents : 0;
                    $totalCogs += (int) round($needQty * $unitCost);

                    // Create negative inventory lot
                    $negLotId = DB::table('inventory_lots')->insertGetId([
                        'product_id' => $c->ingredient_product_id,
                        'location_id'=> $locationId,
                        'lot_code'   => 'NEGATIVE-' . now()->format('YmdHis') . '-' . $order->id,
                        'qty_on_hand'=> -$needQty,
                        'unit_cost_cents'=> $unitCost,
                        'received_at'=> now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    if (DB::getSchemaBuilder()->hasTable('stock_movements')) {
                        DB::table('stock_movements')->insert([
                            'product_id' => $c->ingredient_product_id,
                            'location_id'=> $locationId,
                            'lot_id'     => $negLotId,
                            'direction'  => 'out',
                            'qty'        => $needQty,
                            'unit_cost_cents'=> $unitCost,
                            'reason'     => 'sale_negative',
                            'meta'       => json_encode(['order_id'=>$order->id, 'dish_id'=>$dishId, 'negative_inventory'=>true]),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        return $totalCogs;
    }

    /**
     * Consume ingredient quantity from inventory using FIFO
     * Returns COGS for this consumption
     */
    private function consumeIngredient(int $ingredientId, float $needQty, int $locationId, int $orderId, ?int $comboId = null): int
    {
        $cogs = 0;

        // FIFO consume from inventory_lots for the ingredient at this location
        $lots = DB::table('inventory_lots')
            ->where('product_id', $ingredientId)
            ->where('location_id', $locationId)
            ->where('qty_on_hand', '>', 0)
            ->orderBy('received_at')->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($lots as $lot) {
            if ($needQty <= 0) break;
            $take = min($needQty, (float)$lot->qty_on_hand);
            $needQty -= $take;

            // cost = take * unit_cost
            $cogs += (int) round($take * (int)$lot->unit_cost_cents);

            DB::table('inventory_lots')->where('id', $lot->id)->update([
                'qty_on_hand' => DB::raw('qty_on_hand - ' . (float)$take),
                'updated_at' => now(),
            ]);

            if (DB::getSchemaBuilder()->hasTable('stock_movements')) {
                $meta = ['order_id' => $orderId];
                if ($comboId) {
                    $meta['combo_id'] = $comboId;
                }

                DB::table('stock_movements')->insert([
                    'product_id' => $ingredientId,
                    'location_id' => $locationId,
                    'lot_id' => $lot->id,
                    'direction' => 'out',
                    'qty' => $take,
                    'unit_cost_cents' => $lot->unit_cost_cents,
                    'reason' => 'sale',
                    'meta' => json_encode($meta),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // If needQty > 0 here, we sold more than we have -> create negative inventory lot
        if ($needQty > 0) {
            // Get last lot's cost for valuation, or use 0
            $lastLot = DB::table('inventory_lots')
                ->where('product_id', $ingredientId)
                ->where('location_id', $locationId)
                ->orderByDesc('received_at')->orderByDesc('id')
                ->first();

            $unitCost = $lastLot ? (int)$lastLot->unit_cost_cents : 0;
            $cogs += (int) round($needQty * $unitCost);

            // Create negative inventory lot
            $negLotId = DB::table('inventory_lots')->insertGetId([
                'product_id' => $ingredientId,
                'location_id' => $locationId,
                'lot_code' => 'NEGATIVE-' . now()->format('YmdHis') . '-' . $orderId,
                'qty_on_hand' => -$needQty,
                'unit_cost_cents' => $unitCost,
                'received_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (DB::getSchemaBuilder()->hasTable('stock_movements')) {
                $meta = ['order_id' => $orderId, 'negative_inventory' => true];
                if ($comboId) {
                    $meta['combo_id'] = $comboId;
                }

                DB::table('stock_movements')->insert([
                    'product_id' => $ingredientId,
                    'location_id' => $locationId,
                    'lot_id' => $negLotId,
                    'direction' => 'out',
                    'qty' => $needQty,
                    'unit_cost_cents' => $unitCost,
                    'reason' => 'sale_negative',
                    'meta' => json_encode($meta),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return $cogs;
    }
}
