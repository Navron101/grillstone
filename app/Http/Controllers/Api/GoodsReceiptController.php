<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Imports\GoodsReceiptImport;
use App\Exports\GoodsReceiptTemplateExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class GoodsReceiptController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id'             => 'nullable|integer',
            'po_number'               => 'nullable|string|max:64',
            'purchase_order_id'       => 'nullable|integer', // if your schema uses it
            'received_at'             => 'nullable',         // accept any parseable date
            'location_id'             => 'nullable|integer|min:1',

            'lines'                   => 'required|array|min:1',
            'lines.*.product_id'      => 'required|integer|exists:products,id',
            'lines.*.qty'             => 'required|numeric|min:0.0001',
            'lines.*.unit_name'       => 'nullable|string|max:20',
            'lines.*.unit_cost'       => 'nullable|numeric|min:0',
            'lines.*.unit_cost_cents' => 'nullable|integer|min:0',
            'lines.*.lot_ref'         => 'nullable|string|max:64',
        ]);

        // Normalize datetime to app timezone -> "Y-m-d H:i:s"
        try {
            $receivedAt = isset($data['received_at']) && $data['received_at'] !== ''
                ? Carbon::parse($data['received_at'])
                    ->timezone(config('app.timezone'))
                    ->format('Y-m-d H:i:s')
                : now()->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            $receivedAt = now()->format('Y-m-d H:i:s');
        }

        // Ensure we use a valid locations.id (FK-safe)
        $locationId = $this->resolveValidLocationId((int)($data['location_id'] ?? 0));

        return DB::transaction(function () use ($data, $locationId, $receivedAt) {

            // ── GRN header (only set columns that actually exist) ───────────────────
            $grnId = null;
            if (Schema::hasTable('goods_receipts')) {
                $cols = Schema::getColumnListing('goods_receipts');

                $insert = [
                    'received_at' => $receivedAt,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];

                if (in_array('location_id', $cols))         $insert['location_id']         = $locationId;
                if (in_array('supplier_id', $cols))         $insert['supplier_id']         = $data['supplier_id'] ?? null;
                if (in_array('po_number', $cols))           $insert['po_number']           = $data['po_number'] ?? null;
                if (in_array('purchase_order_id', $cols))   $insert['purchase_order_id']   = $data['purchase_order_id'] ?? null;

                // If your DB has a NOT NULL, no-default purchase_order_id, this try/catch
                // will retry with a safe fallback to avoid 1364/23000 errors.
                try {
                    $grnId = DB::table('goods_receipts')->insertGetId($insert);
                } catch (\Illuminate\Database\QueryException $ex) {
                    $msg = $ex->getMessage();
                    if (str_contains($msg, 'purchase_order_id') && in_array('purchase_order_id', $cols)) {
                        // Fallback value (0) if the column is required w/o default
                        $insert['purchase_order_id'] = $insert['purchase_order_id'] ?? 0;
                        $grnId = DB::table('goods_receipts')->insertGetId($insert);
                    } else {
                        throw $ex;
                    }
                }
            }

            // ── Lines -> inventory_lots (+ optional goods_receipt_lines) ────────────
            $lotsCols    = Schema::hasTable('inventory_lots') ? Schema::getColumnListing('inventory_lots') : [];
            $hasLotRef   = in_array('lot_ref', $lotsCols);
            $hasUnitName = in_array('unit_name', $lotsCols);
            $hasLocation = in_array('location_id', $lotsCols);

            foreach ($data['lines'] as $line) {
                $unitCostCents = $line['unit_cost_cents']
                    ?? (isset($line['unit_cost']) ? (int) round($line['unit_cost'] * 100) : 0);

                $lotInsert = [
                    'product_id'      => (int) $line['product_id'],
                    'qty_on_hand'     => (float) $line['qty'],
                    'unit_cost_cents' => (int) $unitCostCents,
                    'received_at'     => $receivedAt,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
                if ($hasLocation)  $lotInsert['location_id'] = $locationId; // FK-safe
                if ($hasUnitName)  $lotInsert['unit_name']   = $line['unit_name'] ?? null;
                if ($hasLotRef)    $lotInsert['lot_ref']     = $line['lot_ref'] ?? ($data['po_number'] ?? null);

                $lotId = DB::table('inventory_lots')->insertGetId($lotInsert);

                // Optional GRN line (make it adapt to your actual columns)
                if (Schema::hasTable('goods_receipt_lines')) {
                    $grCols = Schema::getColumnListing('goods_receipt_lines');

                    $grLine = [
                        'product_id'      => (int) $line['product_id'],
                        'qty'             => (float) $line['qty'],
                        'unit_cost_cents' => (int) $unitCostCents,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ];

                    if (in_array('goods_receipt_id', $grCols)) {
                        $grLine['goods_receipt_id'] = $grnId;
                    }

                    // Support either inventory_lot_id or lot_id if present
                    if (in_array('inventory_lot_id', $grCols)) {
                        $grLine['inventory_lot_id'] = $lotId;
                    } elseif (in_array('lot_id', $grCols)) {
                        $grLine['lot_id'] = $lotId;
                    }

                    if (in_array('unit_name', $grCols)) {
                        $grLine['unit_name'] = $line['unit_name'] ?? null;
                    }
                    if (in_array('location_id', $grCols)) {
                        $grLine['location_id'] = $locationId;
                    }
                    if (in_array('po_number', $grCols)) {
                        $grLine['po_number'] = $data['po_number'] ?? null;
                    }
                    if (in_array('purchase_order_id', $grCols) && isset($data['purchase_order_id'])) {
                        $grLine['purchase_order_id'] = $data['purchase_order_id'];
                    }

                    DB::table('goods_receipt_lines')->insert($grLine);
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
     * Upload Excel file to import goods receipts
     */
    public function uploadExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
            'location_id' => 'nullable|integer|min:1',
        ]);

        $locationId = $this->resolveValidLocationId((int)($request->input('location_id') ?? 0));

        try {
            $import = new GoodsReceiptImport($locationId);
            Excel::import($import, $request->file('file'));

            $errors = $import->getErrors();
            $successCount = $import->getSuccessCount();

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$successCount} items",
                'imported_count' => $successCount,
                'errors' => $errors,
            ]);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];

            foreach ($failures as $failure) {
                $errors[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }

            return response()->json([
                'success' => false,
                'message' => 'Validation errors occurred',
                'errors' => $errors,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
                'errors' => [$e->getMessage()],
            ], 500);
        }
    }

    /**
     * Download Excel template for goods receipts
     */
    public function downloadTemplate()
    {
        return Excel::download(new GoodsReceiptTemplateExport, 'goods-receipt-template.xlsx');
    }

    /**
     * Ensure we use a valid locations.id:
     * - Use provided id if it exists.
     * - Else use the first location row.
     * - If none exists, create "Main" and return its id.
     */
    private function resolveValidLocationId(int $requestedId = 0): int
    {
        if (!Schema::hasTable('locations')) {
            // Your DB shows a FK on inventory_lots.location_id → locations.id,
            // so this should exist. If not, fail loudly.
            abort(500, 'Locations table missing but location_id is required by FK.');
        }

        if ($requestedId > 0) {
            $exists = DB::table('locations')->where('id', $requestedId)->exists();
            if ($exists) return $requestedId;
        }

        $first = DB::table('locations')->select('id')->orderBy('id')->first();
        if ($first) return (int) $first->id;

        // Seed a default
        return (int) DB::table('locations')->insertGetId([
            'name'       => 'Main',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
