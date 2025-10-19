<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GoodsReceiptTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        // Return sample rows
        return [
            [
                'product_name' => 'Rice',
                'product_id' => '1',
                'quantity' => '50',
                'unit_cost' => '25.50',
                'lot_code' => 'LOT-2025-001',
                'supplier' => 'ABC Suppliers',
                'received_date' => '2025-10-19',
                'expiry_date' => '2026-10-19',
                'reference' => 'PO-001',
                'notes' => 'Sample entry',
            ],
            [
                'product_name' => 'Flour',
                'product_id' => '2',
                'quantity' => '100',
                'unit_cost' => '15.00',
                'lot_code' => 'LOT-2025-002',
                'supplier' => 'XYZ Foods',
                'received_date' => '2025-10-19',
                'expiry_date' => '',
                'reference' => 'PO-001',
                'notes' => '',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'product_name',      // Required - exact product name from system
            'product_id',        // Optional - product ID if known
            'quantity',          // Required - quantity received
            'unit_cost',         // Required - cost per unit in JMD
            'lot_code',          // Optional - lot/batch code
            'supplier',          // Optional - supplier name
            'received_date',     // Optional - date received (YYYY-MM-DD)
            'expiry_date',       // Optional - expiry date (YYYY-MM-DD)
            'reference',         // Optional - PO or invoice reference
            'notes',             // Optional - additional notes
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
