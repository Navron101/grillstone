<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;

class InvoiceOcrController extends Controller
{
    /**
     * Upload and process invoice with Claude AI Vision
     */
    public function upload(Request $request)
    {
        $request->validate([
            'invoice' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240', // 10MB max
        ]);

        $file = $request->file('invoice');
        $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('invoices', $filename, 'public');

        // Create invoice record
        $invoice = Invoice::create([
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'status' => 'pending',
        ]);

        // Process with Claude AI
        try {
            $extractedData = $this->processWithClaudeAI($path);

            $invoice->update([
                'ocr_raw_data' => $extractedData,
                'extracted_items' => $extractedData['items'] ?? [],
                'invoice_number' => $extractedData['invoice_number'] ?? null,
                'supplier_name' => $extractedData['supplier_name'] ?? null,
                'invoice_date' => isset($extractedData['invoice_date']) ? Carbon::parse($extractedData['invoice_date']) : null,
                'total_amount_cents' => isset($extractedData['total_amount']) ? (int)($extractedData['total_amount'] * 100) : null,
                'status' => 'processed',
                'processed_at' => now(),
            ]);
        } catch (\Exception $e) {
            $invoice->update([
                'status' => 'rejected',
                'notes' => 'OCR processing failed: ' . $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process invoice',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'invoice' => $invoice->fresh(),
            'message' => 'Invoice processed successfully',
        ]);
    }

    /**
     * Get all invoices
     */
    public function index()
    {
        $invoices = Invoice::with('processedBy')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'supplier_name' => $invoice->supplier_name,
                    'invoice_date' => $invoice->invoice_date?->format('Y-m-d'),
                    'total_amount_cents' => $invoice->total_amount_cents,
                    'status' => $invoice->status,
                    'original_filename' => $invoice->original_filename,
                    'file_url' => asset('storage/' . $invoice->file_path),
                    'items_count' => count($invoice->extracted_items ?? []),
                    'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json($invoices);
    }

    /**
     * Get single invoice with details
     */
    public function show(Invoice $invoice)
    {
        return response()->json([
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'supplier_name' => $invoice->supplier_name,
            'invoice_date' => $invoice->invoice_date?->format('Y-m-d'),
            'total_amount_cents' => $invoice->total_amount_cents,
            'status' => $invoice->status,
            'original_filename' => $invoice->original_filename,
            'file_url' => asset('storage/' . $invoice->file_path),
            'extracted_items' => $invoice->extracted_items ?? [],
            'notes' => $invoice->notes,
            'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Update invoice data (after user review)
     */
    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'invoice_number' => 'nullable|string',
            'supplier_name' => 'nullable|string',
            'invoice_date' => 'nullable|date',
            'total_amount_cents' => 'nullable|integer',
            'extracted_items' => 'nullable|array',
            'extracted_items.*.product_name' => 'required|string',
            'extracted_items.*.quantity' => 'required|numeric',
            'extracted_items.*.unit_price' => 'required|numeric',
            'extracted_items.*.unit' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $invoice->update($validated);

        return response()->json([
            'success' => true,
            'invoice' => $invoice->fresh(),
            'message' => 'Invoice updated successfully',
        ]);
    }

    /**
     * Create Goods Receipt from invoice
     */
    public function createGoodsReceipt(Request $request, Invoice $invoice)
    {
        if ($invoice->goods_receipt_id) {
            return response()->json([
                'success' => false,
                'message' => 'Goods receipt already created for this invoice',
            ], 400);
        }

        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_cost_cents' => 'required|integer|min:0',
        ]);

        // Create Goods Receipt
        $goodsReceipt = \App\Models\GoodsReceipt::create([
            'location_id' => $validated['location_id'],
            'received_at' => $invoice->invoice_date ?? now(),
            'vendor_name' => $invoice->supplier_name,
            'invoice_number' => $invoice->invoice_number,
            'status' => 'completed',
        ]);

        // Create Goods Receipt Lines
        foreach ($validated['items'] as $item) {
            $line = \App\Models\GoodsReceiptLine::create([
                'goods_receipt_id' => $goodsReceipt->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_cost_cents' => $item['unit_cost_cents'],
            ]);

            // Create inventory lot
            $product = Product::find($item['product_id']);
            $lot = \App\Models\InventoryLot::create([
                'product_id' => $item['product_id'],
                'location_id' => $validated['location_id'],
                'qty_on_hand' => $item['quantity'],
                'unit_cost_cents' => $item['unit_cost_cents'],
                'unit_name' => $product->unit,
                'received_at' => $goodsReceipt->received_at,
                'lot_ref' => 'GRN-' . $goodsReceipt->id,
            ]);

            // Update line with lot ID
            $line->update(['inventory_lot_id' => $lot->id]);

            // Create stock movement
            \App\Models\StockMovement::create([
                'product_id' => $item['product_id'],
                'location_id' => $validated['location_id'],
                'quantity' => $item['quantity'],
                'reason' => 'goods_receipt',
                'reference_type' => 'goods_receipt',
                'reference_id' => $goodsReceipt->id,
                'unit_cost_cents' => $item['unit_cost_cents'],
            ]);
        }

        // Link invoice to goods receipt
        $invoice->update([
            'goods_receipt_id' => $goodsReceipt->id,
            'status' => 'approved',
        ]);

        return response()->json([
            'success' => true,
            'goods_receipt_id' => $goodsReceipt->id,
            'message' => 'Goods receipt created successfully',
        ]);
    }

    /**
     * Delete invoice
     */
    public function destroy(Invoice $invoice)
    {
        if ($invoice->goods_receipt_id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete invoice that has been converted to goods receipt',
            ], 400);
        }

        // Delete file
        if (Storage::disk('public')->exists($invoice->file_path)) {
            Storage::disk('public')->delete($invoice->file_path);
        }

        $invoice->delete();

        return response()->json([
            'success' => true,
            'message' => 'Invoice deleted successfully',
        ]);
    }

    /**
     * Process invoice with Claude AI Vision API
     */
    private function processWithClaudeAI(string $filePath): array
    {
        // Get API key from env
        $apiKey = env('ANTHROPIC_API_KEY');

        if (!$apiKey) {
            throw new \Exception('ANTHROPIC_API_KEY not configured in .env file');
        }

        // Read image file and convert to base64
        $fullPath = storage_path('app/public/' . $filePath);
        $imageData = base64_encode(file_get_contents($fullPath));
        $mimeType = mime_content_type($fullPath);

        // Prepare prompt for Claude
        $prompt = <<<'PROMPT'
You are an expert at extracting structured data from invoices and receipts. Analyze this invoice image and extract the following information with high accuracy:

1. Invoice number
2. Supplier/Vendor name
3. Invoice date
4. Line items with:
   - Product name (match to food/restaurant inventory items)
   - Quantity
   - Unit (kg, lb, oz, pieces, etc.)
   - Unit price
   - Line total

5. Total amount

Return the data in this exact JSON format:
{
  "invoice_number": "string",
  "supplier_name": "string",
  "invoice_date": "YYYY-MM-DD",
  "total_amount": number,
  "currency": "string",
  "items": [
    {
      "product_name": "string",
      "quantity": number,
      "unit": "string",
      "unit_price": number,
      "line_total": number
    }
  ]
}

Important:
- Extract ALL line items from the invoice
- Be precise with numbers - double check quantities and prices
- Normalize product names to be clear and consistent
- If a field is unclear or missing, use null
- For quantities, extract just the number (e.g., "5" from "5 kg")
- For units, normalize to standard units (kg, lb, oz, pieces, dozen, case, etc.)
- Ensure the sum of line_totals matches the total_amount

Return ONLY the JSON object, no additional text.
PROMPT;

        // Call Claude API
        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => 'claude-3-5-sonnet-20241022',
            'max_tokens' => 4096,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'image',
                            'source' => [
                                'type' => 'base64',
                                'media_type' => $mimeType,
                                'data' => $imageData,
                            ],
                        ],
                        [
                            'type' => 'text',
                            'text' => $prompt,
                        ],
                    ],
                ],
            ],
        ]);

        if (!$response->successful()) {
            throw new \Exception('Claude API request failed: ' . $response->body());
        }

        $result = $response->json();

        // Extract JSON from Claude's response
        $content = $result['content'][0]['text'] ?? '';

        // Try to parse JSON (Claude might wrap it in markdown code blocks)
        $content = preg_replace('/```json\s*/', '', $content);
        $content = preg_replace('/```\s*$/', '', $content);
        $content = trim($content);

        $extractedData = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed to parse Claude AI response as JSON: ' . json_last_error_msg());
        }

        return $extractedData;
    }

    /**
     * Match extracted items to existing products
     */
    public function matchProducts(Invoice $invoice)
    {
        $extractedItems = $invoice->extracted_items ?? [];
        $suggestions = [];

        foreach ($extractedItems as $index => $item) {
            $productName = $item['product_name'] ?? '';

            // Search for matching products
            $matches = Product::where('type', 'ingredient')
                ->where(function ($query) use ($productName) {
                    $query->where('name', 'like', '%' . $productName . '%')
                          ->orWhere('description', 'like', '%' . $productName . '%');
                })
                ->limit(5)
                ->get(['id', 'name', 'unit', 'description'])
                ->toArray();

            $suggestions[$index] = [
                'extracted_name' => $productName,
                'matches' => $matches,
                'quantity' => $item['quantity'] ?? null,
                'unit' => $item['unit'] ?? null,
                'unit_price' => $item['unit_price'] ?? null,
            ];
        }

        return response()->json([
            'invoice_id' => $invoice->id,
            'suggestions' => $suggestions,
        ]);
    }
}
