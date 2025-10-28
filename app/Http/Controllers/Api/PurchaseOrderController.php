<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    /**
     * List all purchase orders
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['vendor', 'lines.product']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by vendor
        if ($request->has('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        return response()->json(['purchase_orders' => $orders]);
    }

    /**
     * Get single purchase order
     */
    public function show($id)
    {
        $order = PurchaseOrder::with(['vendor', 'lines.product'])->findOrFail($id);

        return response()->json(['purchase_order' => $order]);
    }

    /**
     * Create new purchase order
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'ordered_at' => 'nullable|date',
            'due_at' => 'nullable|date',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.qty' => 'required|numeric|min:0.01',
            'lines.*.unit_cost_cents' => 'required|integer|min:0',
        ]);

        return DB::transaction(function () use ($data) {
            // Calculate totals
            $subtotal = 0;
            foreach ($data['lines'] as $line) {
                $subtotal += $line['unit_cost_cents'] * $line['qty'];
            }

            $tax = 0; // You can add tax calculation if needed
            $total = $subtotal + $tax;

            // Create purchase order
            $po = PurchaseOrder::create([
                'vendor_id' => $data['vendor_id'],
                'status' => 'draft',
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'ordered_at' => $data['ordered_at'] ?? now(),
                'due_at' => $data['due_at'] ?? null,
                'subtotal_cents' => $subtotal,
                'tax_cents' => $tax,
                'total_cents' => $total,
            ]);

            // Create lines
            foreach ($data['lines'] as $line) {
                PurchaseOrderLine::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $line['product_id'],
                    'qty' => $line['qty'],
                    'unit_cost_cents' => $line['unit_cost_cents'],
                    'line_total_cents' => $line['unit_cost_cents'] * $line['qty'],
                ]);
            }

            return response()->json([
                'message' => 'Purchase order created successfully',
                'purchase_order' => $po->load(['vendor', 'lines.product'])
            ], 201);
        });
    }

    /**
     * Update purchase order
     */
    public function update(Request $request, $id)
    {
        $po = PurchaseOrder::findOrFail($id);

        $data = $request->validate([
            'vendor_id' => 'sometimes|exists:vendors,id',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'ordered_at' => 'nullable|date',
            'due_at' => 'nullable|date',
            'status' => 'sometimes|in:draft,sent,confirmed,received,cancelled',
            'lines' => 'sometimes|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.qty' => 'required|numeric|min:0.01',
            'lines.*.unit_cost_cents' => 'required|integer|min:0',
        ]);

        return DB::transaction(function () use ($po, $data) {
            // Update lines if provided
            if (isset($data['lines'])) {
                // Delete existing lines
                $po->lines()->delete();

                // Calculate new totals
                $subtotal = 0;
                foreach ($data['lines'] as $line) {
                    $subtotal += $line['unit_cost_cents'] * $line['qty'];

                    PurchaseOrderLine::create([
                        'purchase_order_id' => $po->id,
                        'product_id' => $line['product_id'],
                        'qty' => $line['qty'],
                        'unit_cost_cents' => $line['unit_cost_cents'],
                        'line_total_cents' => $line['unit_cost_cents'] * $line['qty'],
                    ]);
                }

                $tax = 0;
                $data['subtotal_cents'] = $subtotal;
                $data['tax_cents'] = $tax;
                $data['total_cents'] = $subtotal + $tax;
            }

            // Update PO
            $po->update($data);

            return response()->json([
                'message' => 'Purchase order updated successfully',
                'purchase_order' => $po->load(['vendor', 'lines.product'])
            ]);
        });
    }

    /**
     * Delete purchase order
     */
    public function destroy($id)
    {
        $po = PurchaseOrder::findOrFail($id);

        if ($po->status !== 'draft') {
            return response()->json([
                'error' => 'Only draft purchase orders can be deleted'
            ], 400);
        }

        $po->lines()->delete();
        $po->delete();

        return response()->json([
            'message' => 'Purchase order deleted successfully'
        ]);
    }

    /**
     * Change PO status
     */
    public function changeStatus(Request $request, $id)
    {
        $data = $request->validate([
            'status' => 'required|in:draft,sent,confirmed,received,cancelled'
        ]);

        $po = PurchaseOrder::findOrFail($id);
        $po->update(['status' => $data['status']]);

        return response()->json([
            'message' => 'Status updated successfully',
            'purchase_order' => $po->load(['vendor', 'lines.product'])
        ]);
    }
}
