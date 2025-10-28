<?php

namespace App\Exports;

use App\Models\LoyaltySettlement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LoyaltySettlementExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, WithColumnWidths
{
    protected $settlement;
    protected $transactionsByEmployee;

    public function __construct(LoyaltySettlement $settlement, $transactionsByEmployee)
    {
        $this->settlement = $settlement;
        $this->transactionsByEmployee = $transactionsByEmployee;
    }

    /**
     * Return collection of all transactions
     */
    public function collection()
    {
        $rows = collect();

        foreach ($this->transactionsByEmployee as $emp) {
            foreach ($emp['transactions'] as $txn) {
                $rows->push((object)[
                    'employee_name' => $emp['employee_name'],
                    'employee_number' => $emp['employee_number'],
                    'date' => $txn['date'],
                    'order_id' => $txn['order_id'],
                    'order_subtotal' => $txn['order_subtotal'],
                    'discount_amount' => $txn['discount_amount'],
                ]);
            }
        }

        return $rows;
    }

    /**
     * Map data for each row
     */
    public function map($row): array
    {
        return [
            $row->employee_name,
            $row->employee_number,
            $row->date,
            $row->order_id,
            '$' . number_format($row->order_subtotal, 2),
            '$' . number_format($row->discount_amount, 2),
        ];
    }

    /**
     * Column headings
     */
    public function headings(): array
    {
        return [
            'Employee Name',
            'Employee Number',
            'Date',
            'Order #',
            'Order Subtotal',
            'Discount Amount',
        ];
    }

    /**
     * Sheet title
     */
    public function title(): string
    {
        return 'Settlement ' . $this->settlement->period;
    }

    /**
     * Apply styles to the sheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '7C3AED'],
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ],
        ];
    }

    /**
     * Column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 25,  // Employee Name
            'B' => 18,  // Employee Number
            'C' => 18,  // Date
            'D' => 12,  // Order #
            'E' => 18,  // Order Subtotal
            'F' => 18,  // Discount Amount
        ];
    }
}
