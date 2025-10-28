<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * List all invoices
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['processedBy', 'goodsReceipt']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by supplier name or invoice number
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('supplier_name', 'like', "%{$search}%")
                  ->orWhere('invoice_number', 'like', "%{$search}%");
            });
        }

        $invoices = $query->orderBy('created_at', 'desc')->get();

        return response()->json(['invoices' => $invoices]);
    }

    /**
     * Get single invoice
     */
    public function show($id)
    {
        $invoice = Invoice::with(['processedBy', 'goodsReceipt'])->findOrFail($id);

        return response()->json(['invoice' => $invoice]);
    }

    /**
     * Create manual invoice
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'invoice_number' => 'required|string|max:255',
            'supplier_name' => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_name' => 'required|string',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.unit_price_cents' => 'required|integer|min:0',
            'total_amount_cents' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $invoice = Invoice::create([
            'invoice_number' => $data['invoice_number'],
            'supplier_name' => $data['supplier_name'],
            'invoice_date' => $data['invoice_date'],
            'status' => 'pending',
            'extracted_items' => $data['items'],
            'total_amount_cents' => $data['total_amount_cents'],
            'notes' => $data['notes'] ?? null,
            'original_filename' => null,
            'file_path' => null,
        ]);

        return response()->json([
            'message' => 'Invoice created successfully',
            'invoice' => $invoice
        ], 201);
    }

    /**
     * Update invoice
     */
    public function update(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        $data = $request->validate([
            'invoice_number' => 'sometimes|string|max:255',
            'supplier_name' => 'sometimes|string|max:255',
            'invoice_date' => 'sometimes|date',
            'items' => 'sometimes|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_name' => 'required|string',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.unit_price_cents' => 'required|integer|min:0',
            'total_amount_cents' => 'sometimes|integer|min:0',
            'status' => 'sometimes|in:pending,approved,rejected,processed,paid',
            'notes' => 'nullable|string',
        ]);

        $invoice->update($data);

        return response()->json([
            'message' => 'Invoice updated successfully',
            'invoice' => $invoice
        ]);
    }

    /**
     * Delete invoice
     */
    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);

        if ($invoice->status === 'processed') {
            return response()->json([
                'error' => 'Cannot delete processed invoices'
            ], 400);
        }

        $invoice->delete();

        return response()->json([
            'message' => 'Invoice deleted successfully'
        ]);
    }

    /**
     * Approve invoice
     */
    public function approve($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->update(['status' => 'approved']);

        return response()->json([
            'message' => 'Invoice approved successfully',
            'invoice' => $invoice
        ]);
    }

    /**
     * Reject invoice
     */
    public function reject(Request $request, $id)
    {
        $data = $request->validate([
            'notes' => 'nullable|string'
        ]);

        $invoice = Invoice::findOrFail($id);
        $invoice->update([
            'status' => 'rejected',
            'notes' => $data['notes'] ?? $invoice->notes
        ]);

        return response()->json([
            'message' => 'Invoice rejected',
            'invoice' => $invoice
        ]);
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid($id)
    {
        $invoice = Invoice::findOrFail($id);

        $invoice->update(['status' => 'paid']);

        return response()->json([
            'message' => 'Invoice marked as paid successfully',
            'invoice' => $invoice
        ]);
    }

    /**
     * Upload and process invoice image with OCR
     */
    public function uploadAndScan(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240', // 10MB max
        ]);

        try {
            // Store the uploaded file
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('invoices', $filename, 'public');
            $fullPath = storage_path('app/public/' . $path);

            // Extract text using OCR
            $ocrService = new \App\Services\OcrService();
            $ocrResult = $ocrService->extractText($fullPath);

            if (!$ocrResult['success']) {
                return response()->json([
                    'error' => 'OCR extraction failed',
                    'details' => $ocrResult['error'] ?? 'Unknown error'
                ], 500);
            }

            // Parse invoice data from OCR text
            $parserService = new \App\Services\InvoiceParserService();
            $parsedData = $parserService->parseInvoiceText($ocrResult['text']);

            // Create invoice record with OCR data
            $invoice = Invoice::create([
                'invoice_number' => $parsedData['invoice_number']['value'] ?? null,
                'supplier_name' => $parsedData['supplier_name']['value'] ?? null,
                'invoice_date' => $parsedData['invoice_date']['value'] ?? null,
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'status' => 'pending', // Requires review
                'ocr_raw_data' => json_encode([
                    'text' => $ocrResult['text'],
                    'confidence' => $ocrResult['confidence'],
                ]),
                'extracted_items' => json_encode($parsedData),
                'total_amount_cents' => $parsedData['total_amount_cents']['value'] ?? 0,
            ]);

            // Calculate overall confidence and flag fields
            $lowConfidenceFields = $this->identifyLowConfidenceFields($parsedData);

            return response()->json([
                'message' => 'Invoice uploaded and scanned successfully',
                'invoice' => $invoice,
                'parsed_data' => $parsedData,
                'needs_review' => !empty($lowConfidenceFields),
                'low_confidence_fields' => $lowConfidenceFields,
                'ocr_confidence' => $ocrResult['confidence'],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to process invoice',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get invoices pending review (low confidence or incomplete)
     */
    public function pendingReview()
    {
        $invoices = Invoice::where('status', 'pending')
            ->whereNotNull('ocr_raw_data')
            ->orderBy('created_at', 'desc')
            ->get();

        $invoicesWithFlags = $invoices->map(function ($invoice) {
            $extractedItems = json_decode($invoice->extracted_items, true);
            $lowConfidenceFields = $this->identifyLowConfidenceFields($extractedItems);

            return [
                'invoice' => $invoice,
                'needs_review' => !empty($lowConfidenceFields),
                'low_confidence_fields' => $lowConfidenceFields,
            ];
        });

        return response()->json([
            'invoices' => $invoicesWithFlags
        ]);
    }

    /**
     * Update invoice after review
     */
    public function updateAfterReview(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        $data = $request->validate([
            'invoice_number' => 'nullable|string',
            'supplier_name' => 'nullable|string',
            'invoice_date' => 'nullable|date',
            'total_amount_cents' => 'nullable|integer',
            'items' => 'nullable|array',
            'items.*.product_name' => 'required|string',
            'items.*.qty' => 'required|numeric',
            'items.*.unit_price_cents' => 'required|integer',
            'notes' => 'nullable|string',
            'status' => 'sometimes|in:pending,approved,processed',
        ]);

        $invoice->update($data);

        // Update extracted_items if items were modified
        if (isset($data['items'])) {
            $extractedItems = json_decode($invoice->extracted_items, true) ?? [];
            $extractedItems['items'] = array_map(function ($item) {
                return [
                    'product_name' => ['value' => $item['product_name'], 'confidence' => 1.0],
                    'qty' => ['value' => $item['qty'], 'confidence' => 1.0],
                    'unit_price_cents' => ['value' => $item['unit_price_cents'], 'confidence' => 1.0],
                ];
            }, $data['items']);

            $invoice->update(['extracted_items' => json_encode($extractedItems)]);
        }

        return response()->json([
            'message' => 'Invoice updated successfully after review',
            'invoice' => $invoice
        ]);
    }

    /**
     * Identify fields with low confidence that need review
     */
    private function identifyLowConfidenceFields(array $parsedData): array
    {
        $lowConfidenceFields = [];
        $threshold = 0.75; // Confidence threshold

        foreach ($parsedData as $field => $data) {
            if ($field === 'items') {
                // Check each item
                foreach ($data as $index => $item) {
                    foreach ($item as $itemField => $itemData) {
                        if ($itemField !== 'source_line' &&
                            isset($itemData['confidence']) &&
                            $itemData['confidence'] < $threshold) {
                            $lowConfidenceFields[] = [
                                'field' => "items.{$index}.{$itemField}",
                                'confidence' => $itemData['confidence'],
                                'value' => $itemData['value'] ?? null,
                            ];
                        }
                    }
                }
            } else {
                if (isset($data['confidence']) && $data['confidence'] < $threshold) {
                    $lowConfidenceFields[] = [
                        'field' => $field,
                        'confidence' => $data['confidence'],
                        'value' => $data['value'] ?? null,
                    ];
                }
            }
        }

        return $lowConfidenceFields;
    }
}
