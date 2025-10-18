<?php

namespace App\Services\Inventory;

use App\Models\InventoryLot;
use App\Models\StockMovement;
use App\Models\OrderItem;
use App\Models\DishIngredient;
use App\Services\Inventory\Contracts\InventoryEngine;
use Illuminate\Support\Facades\DB;

class FifoInventoryEngine implements InventoryEngine
{
    public function expandOrderItem(OrderItem $item): array
    {
        if (($item->item_type ?? 'product') === 'dish' && $item->item_id) {
            $ingredients = DishIngredient::where('dish_id', $item->item_id)->get();
            $map = [];
            foreach ($ingredients as $ing) {
                $map[$ing->product_id] = ($map[$ing->product_id] ?? 0) + ($ing->qty * $item->qty);
            }
            return $map;
        }
        return [$item->product_id => $item->qty];
    }

    public function consume(int $productId, int $locationId, float $qtyNeededCtu): int
    {
        return DB::transaction(function () use ($productId, $locationId, $qtyNeededCtu) {
            $qtyLeft = $qtyNeededCtu;
            $totalCostCents = 0;

            $lots = InventoryLot::query()
                ->where('product_id', $productId)
                ->where('location_id', $locationId)
                ->where('qty_on_hand', '>', 0)
                ->orderBy('expires_at')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($lots as $lot) {
                if ($qtyLeft <= 0) break;
                $take = min($lot->qty_on_hand, $qtyLeft);
                if ($take <= 0) continue;

                $lot->qty_on_hand -= $take;
                $lot->save();

                $lineCost = (int) round($take * $lot->unit_cost_cents);
                $totalCostCents += $lineCost;

                StockMovement::create([
                    'product_id'       => $productId,
                    'location_id'      => $locationId,
                    'qty_delta'        => -$take,
                    'reason'           => 'sale',
                    'ref_type'         => 'order',
                    'ref_id'           => null, // fill after order id exists
                    'unit_cost_cents'  => $lot->unit_cost_cents,
                ]);

                $qtyLeft -= $take;
            }

            if ($qtyLeft > 0) {
                throw new \RuntimeException('Insufficient stock for product '.$productId.' at location '.$locationId);
            }

            return $totalCostCents;
        });
    }
}
