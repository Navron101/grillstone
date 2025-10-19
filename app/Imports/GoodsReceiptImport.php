<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class GoodsReceiptImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected $locationId;
    protected $errors = [];
    protected $successCount = 0;

    public function __construct($locationId = 1)
    {
        $this->locationId = $locationId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                $this->processRow($row, $index + 2); // +2 because of header row
            } catch (\Exception $e) {
                $this->errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
            }
        }
    }

    protected function processRow($row, $rowNumber)
    {
        // Find product by name or SKU
        $product = Product::where('name', $row['product_name'])
            ->orWhere('id', $row['product_id'] ?? null)
            ->first();

        if (!$product) {
            throw new \Exception("Product '{$row['product_name']}' not found");
        }

        // Validate quantity
        $qty = (float) ($row['quantity'] ?? 0);
        if ($qty <= 0) {
            throw new \Exception("Invalid quantity: {$qty}");
        }

        // Validate unit cost
        $unitCostCents = (int) (($row['unit_cost'] ?? 0) * 100);
        if ($unitCostCents < 0) {
            throw new \Exception("Invalid unit cost: {$row['unit_cost']}");
        }

        DB::transaction(function () use ($product, $qty, $unitCostCents, $row) {
            // Create goods receipt
            $grn = DB::table('goods_receipts')->insertGetId([
                'reference' => $row['reference'] ?? 'EXCEL-' . Str::upper(Str::random(6)),
                'location_id' => $this->locationId,
                'supplier' => $row['supplier'] ?? 'Excel Import',
                'received_at' => $row['received_date'] ?? now(),
                'notes' => $row['notes'] ?? 'Imported from Excel',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create inventory lot
            $lotId = DB::table('inventory_lots')->insertGetId([
                'product_id' => $product->id,
                'location_id' => $this->locationId,
                'lot_code' => $row['lot_code'] ?? 'LOT-' . now()->format('YmdHis'),
                'qty_on_hand' => $qty,
                'unit_cost_cents' => $unitCostCents,
                'received_at' => $row['received_date'] ?? now(),
                'expires_at' => isset($row['expiry_date']) ? $row['expiry_date'] : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create GRN line
            DB::table('goods_receipt_lines')->insert([
                'goods_receipt_id' => $grn,
                'inventory_lot_id' => $lotId,
                'product_id' => $product->id,
                'qty_received' => $qty,
                'unit_cost_cents' => $unitCostCents,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create stock movement
            if (DB::getSchemaBuilder()->hasTable('stock_movements')) {
                DB::table('stock_movements')->insert([
                    'product_id' => $product->id,
                    'location_id' => $this->locationId,
                    'lot_id' => $lotId,
                    'direction' => 'in',
                    'qty' => $qty,
                    'unit_cost_cents' => $unitCostCents,
                    'reason' => 'goods_receipt',
                    'meta' => json_encode(['grn_id' => $grn, 'source' => 'excel_import']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        $this->successCount++;
    }

    public function rules(): array
    {
        return [
            'product_name' => 'required|string',
            'quantity' => 'required|numeric|min:0.01',
            'unit_cost' => 'required|numeric|min:0',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'product_name.required' => 'Product name is required',
            'quantity.required' => 'Quantity is required',
            'quantity.min' => 'Quantity must be greater than 0',
            'unit_cost.required' => 'Unit cost is required',
            'unit_cost.min' => 'Unit cost cannot be negative',
        ];
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }
}
