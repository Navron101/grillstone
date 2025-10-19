<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        // Return sample rows
        return [
            [
                'name' => 'Coca Cola 500ml',
                'category' => 'Beverages',
                'price' => '150.00',
                'unit_name' => 'bottle',
                'description' => 'Coca Cola soft drink 500ml bottle',
                'image_url' => '',
                'low_stock_threshold' => '20',
            ],
            [
                'name' => 'Bottled Water 1L',
                'category' => 'Beverages',
                'price' => '100.00',
                'unit_name' => 'bottle',
                'description' => 'Pure spring water 1 liter',
                'image_url' => '',
                'low_stock_threshold' => '30',
            ],
            [
                'name' => 'Chocolate Bar',
                'category' => 'Snacks',
                'price' => '120.00',
                'unit_name' => 'ea',
                'description' => 'Milk chocolate bar',
                'image_url' => '',
                'low_stock_threshold' => '15',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'name',                  // Required - product name
            'category',              // Optional - product category
            'price',                 // Required - selling price in JMD
            'unit_name',             // Optional - unit (ea, bottle, can, box, pack, etc.)
            'description',           // Optional - product description
            'image_url',             // Optional - product image URL
            'low_stock_threshold',   // Optional - low stock alert level (default: 5)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
