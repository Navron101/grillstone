<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductsImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected $errors = [];
    protected $successCount = 0;

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
        // Check if product already exists
        $existing = DB::table('products')
            ->where('name', $row['name'])
            ->where('type', 'product')
            ->first();

        if ($existing) {
            throw new \Exception("Product '{$row['name']}' already exists");
        }

        // Validate price
        $priceCents = isset($row['price']) ? (int) ($row['price'] * 100) : 0;
        if ($priceCents < 0) {
            throw new \Exception("Invalid price: {$row['price']}");
        }

        // Create product
        DB::table('products')->insert([
            'name' => $row['name'],
            'category' => $row['category'] ?? null,
            'price_cents' => $priceCents,
            'unit_name' => $row['unit_name'] ?? 'ea',
            'description' => $row['description'] ?? null,
            'image_url' => $row['image_url'] ?? null,
            'low_stock_threshold' => $row['low_stock_threshold'] ?? 5,
            'type' => 'product',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->successCount++;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:120',
            'category' => 'nullable|string|max:64',
            'price' => 'required|numeric|min:0',
            'unit_name' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:500',
            'low_stock_threshold' => 'nullable|numeric|min:0',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => 'Product name is required',
            'name.max' => 'Product name cannot exceed 120 characters',
            'price.required' => 'Price is required',
            'price.min' => 'Price cannot be negative',
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
