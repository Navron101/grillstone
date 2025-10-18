<?php


namespace App\Services;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;


class InventoryService
{
/**
* Add a stock movement and (optionally) touch lots. Returns movement id.
* $reason: receive | sale | variance | transfer | waste | production
*/
public function move(int $productId, int $locationId, float $qtyDelta, string $reason, array $opts = []): int
{
$movementId = (int) DB::table('stock_movements')->insertGetId([
'product_id' => $productId,
'location_id' => $locationId,
'qty_delta' => $qtyDelta, // negative for sale
'reason' => $reason,
'ref_type' => $opts['ref_type'] ?? null,
'ref_id' => $opts['ref_id'] ?? null,
'unit_cost_cents' => $opts['unit_cost_cents'] ?? null, // set when known
'created_by' => $opts['created_by'] ?? null,
'created_at' => now(),
]);


// Optionally update inventory_lots directly if provided
if (($opts['touch_lots'] ?? false) === true) {
$this->applyToLots($productId, $locationId, $qtyDelta, $opts['unit_cost_cents'] ?? null);
}


return $movementId;
}


/**
* FIFO application to inventory_lots; negative qty consumes oldest lots first; positive qty creates/updates a lot.
* If consuming more than available, leftover becomes negative on the last lot (or creates a virtual lot) to allow oversell if desired.
*/
public function applyToLots(int $productId, int $locationId, float $qtyDelta, ?int $unitCostCents = null): void
{
if ($qtyDelta > 0) {
// Receiving: append to (or create) a generic lot with the given cost
DB::table('inventory_lots')->insert([
'product_id' => $productId,
'location_id' => $locationId,
'qty_on_hand' => $qtyDelta,
'unit_cost_cents' => $unitCostCents,
'expires_at' => null,
'lot_code' => null,
'created_at' => now(),
'updated_at' => now(),
]);
return;
}


if ($qtyDelta < 0) {
$remaining = abs($qtyDelta);
$lots = DB::table('inventory_lots')
->where('product_id', $productId)
->where('location_id', $locationId)
}