<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IngredientsTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        // Return sample rows
        return [
            [
                'name' => 'Beef',
                'unit_name' => 'kg',
                'price' => '450.00',
                'low_stock_threshold' => '10',
            ],
            [
                'name' => 'Tomatoes',
                'unit_name' => 'kg',
                'price' => '80.00',
                'low_stock_threshold' => '5',
            ],
            [
                'name' => 'Olive Oil',
                'unit_name' => 'L',
                'price' => '1200.00',
                'low_stock_threshold' => '3',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'name',                  // Required - ingredient name
            'unit_name',             // Optional - unit (kg, L, g, ml, pcs, etc.)
            'price',                 // Optional - default price in JMD
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
