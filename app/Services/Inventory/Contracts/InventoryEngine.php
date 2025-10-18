<?php

namespace App\Services\Inventory\Contracts;

use App\Models\OrderItem;

interface InventoryEngine
{
    public function consume(int $productId, int $locationId, float $qtyNeededCtu): int;
    public function expandOrderItem(OrderItem $item): array;
}
