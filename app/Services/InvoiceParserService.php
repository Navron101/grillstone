<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class InvoiceParserService
{
    /**
     * Parse invoice data from OCR text
     *
     * This service extracts structured invoice data from raw OCR text
     * and assigns confidence scores to each field
     */
    public function parseInvoiceText(string $text): array
    {
        try {
            $lines = explode("\n", $text);

            return [
                'invoice_number' => $this->extractInvoiceNumber($lines),
                'supplier_name' => $this->extractSupplierName($lines),
                'invoice_date' => $this->extractInvoiceDate($lines),
                'items' => $this->extractLineItems($lines),
                'total_amount_cents' => $this->extractTotalAmount($lines),
                'subtotal_cents' => $this->extractSubtotal($lines),
                'tax_cents' => $this->extractTax($lines),
                'notes' => $this->extractNotes($lines),
            ];

        } catch (\Exception $e) {
            Log::error('Invoice parsing failed: ' . $e->getMessage());
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Extract invoice number with confidence score
     */
    private function extractInvoiceNumber(array $lines): array
    {
        foreach ($lines as $line) {
            // Pattern: INV-YYYY-NNNNNN, Invoice #: XXXXX, etc.
            if (preg_match('/(?:invoice|inv)[\s#:]+([A-Z0-9-]+)/i', $line, $matches)) {
                return [
                    'value' => trim($matches[1]),
                    'confidence' => 0.9,
                    'source_line' => $line,
                ];
            }
        }

        return ['value' => null, 'confidence' => 0, 'source_line' => null];
    }

    /**
     * Extract supplier name with confidence score
     */
    private function extractSupplierName(array $lines): array
    {
        // Usually the supplier name is in the first few lines
        $possibleNames = [];

        for ($i = 0; $i < min(5, count($lines)); $i++) {
            $line = trim($lines[$i]);

            // Skip empty lines, dates, and common keywords
            if (empty($line) ||
                preg_match('/^\d{1,2}\/\d{1,2}\/\d{2,4}/', $line) ||
                preg_match('/^(invoice|receipt|tel|email|address)/i', $line)) {
                continue;
            }

            // Check if line looks like a company name
            if (preg_match('/^[A-Z][A-Za-z\s&,\.]+(?:Ltd|Limited|Inc|Corp|Company)?$/i', $line)) {
                $possibleNames[] = [
                    'value' => $line,
                    'confidence' => 0.7 + ($i === 0 ? 0.2 : 0),
                    'source_line' => $line,
                ];
            }
        }

        return !empty($possibleNames) ? $possibleNames[0] :
            ['value' => null, 'confidence' => 0, 'source_line' => null];
    }

    /**
     * Extract invoice date with confidence score
     */
    private function extractInvoiceDate(array $lines): array
    {
        foreach ($lines as $line) {
            // Pattern: Date: MM/DD/YYYY, DD-MM-YYYY, etc.
            if (preg_match('/(?:date|dated)[\s:]+(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4})/i', $line, $matches)) {
                $dateStr = $matches[1];
                try {
                    $date = \Carbon\Carbon::parse($dateStr)->format('Y-m-d');
                    return [
                        'value' => $date,
                        'confidence' => 0.85,
                        'source_line' => $line,
                    ];
                } catch (\Exception $e) {
                    // Continue searching
                }
            }

            // Alternative pattern: October 26, 2025
            if (preg_match('/([A-Za-z]+\s+\d{1,2},?\s+\d{4})/', $line, $matches)) {
                try {
                    $date = \Carbon\Carbon::parse($matches[1])->format('Y-m-d');
                    return [
                        'value' => $date,
                        'confidence' => 0.8,
                        'source_line' => $line,
                    ];
                } catch (\Exception $e) {
                    // Continue searching
                }
            }
        }

        return ['value' => null, 'confidence' => 0, 'source_line' => null];
    }

    /**
     * Extract line items with confidence scores
     */
    private function extractLineItems(array $lines): array
    {
        $items = [];
        $inItemsSection = false;

        foreach ($lines as $line) {
            // Detect items section
            if (preg_match('/^(ITEMS|PRODUCTS|DESCRIPTION)/i', $line)) {
                $inItemsSection = true;
                continue;
            }

            // Stop at total/subtotal
            if (preg_match('/^(TOTAL|SUBTOTAL|TAX)/i', $line)) {
                break;
            }

            if ($inItemsSection) {
                // Pattern: Qty Description @ Price = Total
                // Example: 1. Chicken Breast (Fresh) - 50 lbs @ JMD 450.00 = JMD 22,500.00
                if (preg_match('/^(\d+\.?)?\s*(.+?)\s*-\s*([\d.]+)\s*(?:lbs?|kg|gal|pcs|units?)?\s*@\s*(?:JMD|USD|\$)?\s*([\d,]+\.?\d*)\s*=\s*(?:JMD|USD|\$)?\s*([\d,]+\.?\d*)/', $line, $matches)) {
                    $description = trim($matches[2]);
                    $quantity = floatval($matches[3]);
                    $unitPrice = floatval(str_replace(',', '', $matches[4]));
                    $lineTotal = floatval(str_replace(',', '', $matches[5]));

                    $items[] = [
                        'product_name' => [
                            'value' => $description,
                            'confidence' => 0.8,
                        ],
                        'qty' => [
                            'value' => $quantity,
                            'confidence' => 0.85,
                        ],
                        'unit_price_cents' => [
                            'value' => (int)($unitPrice * 100),
                            'confidence' => 0.85,
                        ],
                        'line_total_cents' => [
                            'value' => (int)($lineTotal * 100),
                            'confidence' => 0.9,
                        ],
                        'source_line' => $line,
                    ];
                }
            }
        }

        return $items;
    }

    /**
     * Extract total amount with confidence score
     */
    private function extractTotalAmount(array $lines): array
    {
        foreach ($lines as $line) {
            // Pattern: Total: JMD 78,754.00
            if (preg_match('/^Total[\s:]+(?:JMD|USD|\$)?\s*([\d,]+\.?\d*)/i', $line, $matches)) {
                $amount = floatval(str_replace(',', '', $matches[1]));
                return [
                    'value' => (int)($amount * 100),
                    'confidence' => 0.9,
                    'source_line' => $line,
                ];
            }
        }

        return ['value' => null, 'confidence' => 0, 'source_line' => null];
    }

    /**
     * Extract subtotal with confidence score
     */
    private function extractSubtotal(array $lines): array
    {
        foreach ($lines as $line) {
            if (preg_match('/^Subtotal[\s:]+(?:JMD|USD|\$)?\s*([\d,]+\.?\d*)/i', $line, $matches)) {
                $amount = floatval(str_replace(',', '', $matches[1]));
                return [
                    'value' => (int)($amount * 100),
                    'confidence' => 0.85,
                    'source_line' => $line,
                ];
            }
        }

        return ['value' => null, 'confidence' => 0, 'source_line' => null];
    }

    /**
     * Extract tax amount with confidence score
     */
    private function extractTax(array $lines): array
    {
        foreach ($lines as $line) {
            if (preg_match('/^Tax[\s\(]+[\d.]+%?\)?[\s:]+(?:JMD|USD|\$)?\s*([\d,]+\.?\d*)/i', $line, $matches)) {
                $amount = floatval(str_replace(',', '', $matches[1]));
                return [
                    'value' => (int)($amount * 100),
                    'confidence' => 0.8,
                    'source_line' => $line,
                ];
            }
        }

        return ['value' => null, 'confidence' => 0, 'source_line' => null];
    }

    /**
     * Extract notes/additional information
     */
    private function extractNotes(array $lines): array
    {
        $notes = [];

        foreach ($lines as $line) {
            if (preg_match('/^(Payment Terms|Due Date|Notes?)[\s:]+(.+)/i', $line, $matches)) {
                $notes[] = trim($matches[2]);
            }
        }

        $notesText = implode('; ', $notes);

        return [
            'value' => $notesText ?: null,
            'confidence' => $notesText ? 0.7 : 0,
            'source_line' => null,
        ];
    }
}
