<?php

namespace App\Exports;

use App\Models\Stocktake;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StocktakeVarianceExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $stocktake;

    public function __construct($stocktakeId)
    {
        $this->stocktake = Stocktake::with(['lines.product', 'location'])
            ->findOrFail($stocktakeId);
    }

    public function collection()
    {
        $data = collect();

        // Header info
        $data->push(['Stocktake Variance Report']);
        $data->push(['Reference:', $this->stocktake->reference]);
        $data->push(['Location:', $this->stocktake->location->name ?? 'N/A']);
        $data->push(['Status:', ucfirst($this->stocktake->status)]);
        $data->push(['Date:', $this->stocktake->counted_at ? $this->stocktake->counted_at->format('Y-m-d H:i') : 'N/A']);
        $data->push([]); // Empty row

        // Column headers
        $data->push(['Product', 'System Qty', 'Actual Qty', 'Variance', 'Unit Cost (JMD)', 'Value Impact (JMD)', 'Notes']);

        // Lines
        $totalVarianceValue = 0;
        foreach ($this->stocktake->lines as $line) {
            $variance = $line->variance ?? 0;
            $unitCost = ($line->unit_cost_cents ?? 0) / 100;
            $valueImpact = $variance * $unitCost;
            $totalVarianceValue += $valueImpact;

            $data->push([
                $line->product->name ?? 'Unknown',
                number_format($line->system_qty, 2),
                $line->actual_qty !== null ? number_format($line->actual_qty, 2) : '-',
                $variance != 0 ? number_format($variance, 2) : '0.00',
                number_format($unitCost, 2),
                number_format($valueImpact, 2),
                $line->notes ?? '',
            ]);
        }

        // Summary
        $data->push([]); // Empty row
        $data->push(['Total Items:', count($this->stocktake->lines)]);
        $data->push(['Items Counted:', $this->stocktake->lines->filter(fn($l) => $l->actual_qty !== null)->count()]);
        $data->push(['Items with Variance:', $this->stocktake->lines->filter(fn($l) => $l->variance != 0)->count()]);
        $data->push(['Total Variance Value (JMD):', number_format($totalVarianceValue, 2)]);

        return $data;
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            7 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E2E8F0']]],
        ];
    }

    public function title(): string
    {
        return 'Variance Report';
    }
}
