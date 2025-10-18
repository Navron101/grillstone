<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class GoodsReceiptController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id'             => 'nullable|integer',
            'po_number'               => 'nullable|string|max:64',
            'purchase_order_id'       => 'nullable|integer',
            'received_at'             => 'nullable', // accept anything Carbon can parse
            'location_id'             => 'nullable|integer|min:1',
            'lines'                   => 'required|array|min:1',
            'lines.*.product_id'      => 'required|integer|exists:products,id',
            'lines.*.qty'             => 'required|numeric|min:0.0001',
            'lines.*.unit_name'       => 'nullable|string|max:20',
            'lines.*.unit_cost'       => 'nullable|numeric|min:0',
            'lines.*.unit_cost_cents' => 'nullable|integer|min:0',
            'lines.*.lot_ref'         => 'nullable|string|max:64',
        ]);

        // 1) Normalize date (handles ISO like 2025-10-18T07:36:00.000Z)
        try {
            $receivedAt = isset($data['received_at']) && $data['received_at'] !== ''
                ? Carbon::parse($data['received_at'])->timezone(config('app.timezone'))->format('Y-m-d H:i:s')
                : now()->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            $receivedAt = now()->format('Y-m-d H:i:s');
        }

        // 2) Resolve a FK-safe location id. Will create “Main” if empty.
        $locationId = $this->resolveValidLocationId((int)($data['location_id'] ?? 0));

        // 3) Preload column lists (so we only insert what exists)
        $hasGoodsReceipts      = Schema::hasTable('goods_receipts');
        $grCols                = $hasGoodsReceipts ? Schema::getColumnListing('goods_receipts') : [];
        $hasLots               = Schema::hasTable('inventory_lots');
        $lotCols               = $hasLots ? Schema::getColumnListing('inventory_lots') : [];
        $hasGoodsReceiptLines  = Schema::hasTable('goods_receipt_lines');
        $grLineCols            = $hasGoodsReceiptLines ? Schema::getColumnListing('goods_receipt_lines') : [];

        // 4) Optional: try to resolve purchase_order_id from po_number if both schema + data suggest it
        $purchaseOrderId = null;
        if (in_array('purchase_order_id', $grCols)) {
            if (!empty($data['purchase_order_id'])) {
                $purchaseOrderId = (int) $data['purchase_order_id'];
            } elseif (!empty($data['po_number']) && Schema::hasTable('purchase_orders')) {
                $po = DB::table('purchase_orders')
                    ->select('id')
                    ->where('number', $data['po_number'])
                    ->first();
                if ($po) $purchaseOrderId = (int) $po->id;
            }
            // If your DB has NOT NULL on purchase_order_id with no default and you didn’t pass anything,
            // this will still be omitted from insert to avoid “cannot be null” errors.
            // If the DB requires it, set a default at the DB level or always supply it.
        }

        return DB::transaction(function () use ($data, $receivedAt, $locationId, $hasGoodsReceipts, $grCols, $hasLots, $lotCols, $hasGoodsReceiptLines, $grLineCols, $purchaseOrderId) {

            // 5) Insert header (goods_receipts) – only columns that actually exist
            $grnId = null;
            if ($hasGoodsReceipts) {
                $insert = [];

                // always safe columns
                if (in_array('received_at', $grCols)) $insert['received_at'] = $receivedAt;
                if (in_array('location_id', $grCols)) $insert['location_id'] = $locationId;

                // optional user payload columns
                if (in_array('supplier_id', $grCols) && array_key_exists('supplier_id', $data)) {
                    $insert['supplier_id'] = $data['supplier_id'];
                }
                if (in_array('po_number', $grCols) && array_key_exists('po_number', $data)) {
                    $insert['po_number'] = $data['po_number'];
                }
                if (in_array('purchase_order_id', $grCols) && $purchaseOrderId !== null) {
                    $insert['purchase_order_id'] = $purchaseOrderId;
                }

                // timestamps only if present
                if (in_array('created_at', $grCols)) $insert['created_at'] = now();
                if (in_array('updated_at', $grCols)) $insert['updated_at'] = now();

                $grnId = DB::table('goods_receipts')->insertGetId($insert);
            }

            // 6) Insert detail rows
            foreach ($data['lines'] as $line) {
                $unitCostCents = $line['unit_cost_cents']
                    ?? (isset($line['unit_cost']) ? (int) round($line['unit_cost'] * 100) : 0);

                // inventory_lots
                if ($hasLots) {
                    $lotInsert = [];

                    if (in_array('product_id', $lotCols))       $lotInsert['product_id'] = (int)$line['product_id'];
                    if (in_array('qty_on_hand', $lotCols))       $lotInsert['qty_on_hand'] = (float)$line['qty'];
                    if (in_array('unit_cost_cents', $lotCols))   $lotInsert['unit_cost_cents'] = (int)$unitCostCents;
                    if (in_array('received_at', $lotCols))       $lotInsert['received_at'] = $receivedAt;

                    // FK-safe location
                    if (in_array('location_id', $lotCols))       $lotInsert['location_id'] = $locationId;

                    // optional extras
                    if (in_array('unit_name', $lotCols))         $lotInsert['unit_name'] = $line['unit_name'] ?? null;
                    if (in_array('lot_ref', $lotCols))           $lotInsert['lot_ref']   = $line['lot_ref'] ?? ($data['po_number'] ?? null);

                    if (in_array('created_at', $lotCols))        $lotInsert['created_at'] = now();
                    if (in_array('updated_at', $lotCols))        $lotInsert['updated_at'] = now();

                    $lotId = DB::table('inventory_lots')->insertGetId($lotInsert);
                } else {
                    $lotId = null;
                }

                // goods_receipt_lines (optional)
                if ($hasGoodsReceiptLines) {
                    $lineInsert = [];

                    if (in_array('goods_receipt_id', $grLineCols)) $lineInsert['goods_receipt_id'] = $grnId;
                    if (in_array('inventory_lot_id', $grLineCols)) $lineInsert['inventory_lot_id'] = $lotId;
                    if (in_array('product_id', $grLineCols))       $lineInsert['product_id']       = (int)$line['product_id'];
                    if (in_array('qty', $grLineCols))              $lineInsert['qty']              = (float)$line['qty'];
                    if (in_array('unit_cost_cents', $grLineCols))  $lineInsert['unit_cost_cents']  = (int)$unitCostCents;
                    if (in_array('unit_name', $grLineCols))        $lineInsert['unit_name']        = $line['unit_name'] ?? null;

                    if (in_array('created_at', $grLineCols))       $lineInsert['created_at'] = now();
                    if (in_array('updated_at', $grLineCols))       $lineInsert['updated_at'] = now();

                    // If the table literally only has a subset, we’ll still insert that subset.
                    if (!empty($lineInsert)) {
                        DB::table('goods_receipt_lines')->insert($lineInsert);
                    }
                }
            }

            return response()->json([
                'ok'               => true,
                'goods_receipt_id' => $grnId,
                'received_at'      => $receivedAt,
                'location_id'      => $locationId,
            ], 201);
        });
    }

    /**
     * Ensure we use a valid locations.id:
     * - Use provided id if it exists.
     * - Else use the first location row.
     * - If none exists, create “Main” and return its id.
     * If the locations table itself is missing, we hard-fail (since you have FKs there).
     */
    private function resolveValidLocationId(int $requestedId = 0): int
    {
        if (!Schema::hasTable('locations')) {
            abort(500, 'Locations table missing but location_id is required by FK.');
        }

        if ($requestedId > 0) {
            $exists = DB::table('locations')->where('id', $requestedId)->exists();
            if ($exists) return $requestedId;
        }

        $first = DB::table('locations')->select('id')->orderBy('id')->first();
        if ($first) return (int)$first->id;

        // Create a default “Main” row so FKs won’t fail
        return (int) DB::table('locations')->insertGetId([
            'name'       => 'Main',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
