<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class IngredientsImport implements ToCollection, WithHeadingRow, WithValidation
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
        // Check if ingredient already exists
        $existing = DB::table('products')
            ->where('name', $row['name'])
            ->where('type', 'ingredient')
            ->first();

        if ($existing) {
            throw new \Exception("Ingredient '{$row['name']}' already exists");
        }

        // Validate price
        $priceCents = isset($row['price']) ? (int) ($row['price'] * 100) : 0;
        if ($priceCents < 0) {
            throw new \Exception("Invalid price: {$row['price']}");
        }

        // Create ingredient
        DB::table('products')->insert([
            'name' => $row['name'],
            'unit_name' => $row['unit_name'] ?? 'kg',
            'price_cents' => $priceCents,
            'type' => 'ingredient',
            'low_stock_threshold' => $row['low_stock_threshold'] ?? 5,
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
            'unit_name' => 'nullable|string|max:20',
            'price' => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'nullable|numeric|min:0',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => 'Ingredient name is required',
            'name.max' => 'Ingredient name cannot exceed 120 characters',
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
