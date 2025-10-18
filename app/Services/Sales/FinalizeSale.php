<?php

namespace App\Services\Sales;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StockMovement;
use App\Services\Inventory\Contracts\InventoryEngine;
use Illuminate\Support\Facades\DB;

class FinalizeSale
{
    public function __construct(private InventoryEngine $inventory) {}

    public function applyInventory(Order $order, int $locationId): int
    {
        $consumptionMap = [];
        foreach ($order->items as $oi) {
            $parts = method_exists($this->inventory, 'expandOrderItem')
                ? $this->inventory->expandOrderItem($oi)
                : [$oi->product_id => $oi->qty];
            foreach ($parts as $pid => $q) {
                $consumptionMap[$pid] = ($consumptionMap[$pid] ?? 0) + $q;
            }
        }

        $cogsCents = 0;
        foreach ($consumptionMap as $pid => $qty) {
            $cogsCents += $this->inventory->consume($pid, $locationId, $qty);
            StockMovement::whereNull('ref_id')
                ->where('ref_type','order')->where('product_id',$pid)
                ->latest('id')->take(10)->update(['ref_id'=>$order->id]);
        }
        return $cogsCents;
    }
}
