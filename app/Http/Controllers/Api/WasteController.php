<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WasteRecord;
use App\Models\Product;
use App\Models\InventoryLot;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WasteController extends Controller
{
    /**
     * Display a listing of waste records.
     */
    public function index(Request $request)
    {
        $query = WasteRecord::with(['product', 'user'])
            ->orderBy('wasted_at', 'desc');

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('from_date')) {
            $query->where('wasted_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('wasted_at', '<=', $request->to_date);
        }

        $perPage = $request->input('per_page', 50);
        return $query->paginate($perPage);
    }

    /**
     * Store a newly created waste record and deduct from inventory.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.001',
            'unit' => 'required|string',
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'wasted_at' => 'nullable|date',
            'location_id' => 'required|exists:locations,id',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $locationId = $validated['location_id'];
        $quantity = $validated['quantity'];

        return DB::transaction(function () use ($validated, $product, $locationId, $quantity, $request) {
            // Calculate cost using FIFO
            $totalCostCents = $this->consumeInventoryFifo(
                $product->id,
                $locationId,
                $quantity
            );

            // Create waste record
            $wasteRecord = WasteRecord::create([
                'product_id' => $product->id,
                'user_id' => $request->user()->id ?? null,
                'quantity' => $quantity,
                'unit' => $validated['unit'],
                'cost' => $totalCostCents / 100, // Convert cents to dollars
                'reason' => $validated['reason'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'wasted_at' => $validated['wasted_at'] ?? now(),
            ]);

            return response()->json([
                'message' => 'Waste recorded successfully',
                'waste_record' => $wasteRecord->load(['product', 'user']),
            ], 201);
        });
    }

    /**
     * Display the specified waste record.
     */
    public function show(string $id)
    {
        $wasteRecord = WasteRecord::with(['product', 'user'])->findOrFail($id);
        return response()->json($wasteRecord);
    }

    /**
     * Update the specified waste record.
     */
    public function update(Request $request, string $id)
    {
        $wasteRecord = WasteRecord::findOrFail($id);

        $validated = $request->validate([
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $wasteRecord->update($validated);

        return response()->json([
            'message' => 'Waste record updated successfully',
            'waste_record' => $wasteRecord->load(['product', 'user']),
        ]);
    }

    /**
     * Remove the specified waste record.
     */
    public function destroy(string $id)
    {
        $wasteRecord = WasteRecord::findOrFail($id);
        $wasteRecord->delete();

        return response()->json([
            'message' => 'Waste record deleted successfully',
        ]);
    }

    /**
     * Consume inventory using FIFO method and create stock movements.
     */
    private function consumeInventoryFifo(int $productId, int $locationId, float $quantity): int
    {
        $qtyLeft = $quantity;
        $totalCostCents = 0;

        $lots = InventoryLot::query()
            ->where('product_id', $productId)
            ->where('location_id', $locationId)
            ->where('qty_on_hand', '>', 0)
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->lockForUpdate()
            ->get();

        foreach ($lots as $lot) {
            if ($qtyLeft <= 0) break;

            $take = min($lot->qty_on_hand, $qtyLeft);
            if ($take <= 0) continue;

            $lot->qty_on_hand -= $take;
            $lot->save();

            $lineCost = (int) round($take * $lot->unit_cost_cents);
            $totalCostCents += $lineCost;

            DB::table('stock_movements')->insert([
                'product_id'       => $productId,
                'location_id'      => $locationId,
                'qty_delta'        => -$take,
                'reason'           => 'waste',
                'ref_type'         => 'waste_record',
                'ref_id'           => null, // Will be updated after waste record is created
                'unit_cost_cents'  => $lot->unit_cost_cents,
                'created_at'       => now(),
            ]);

            $qtyLeft -= $take;
        }

        if ($qtyLeft > 0) {
            throw new \RuntimeException("Insufficient stock for product {$productId} at location {$locationId}. Needed: {$quantity}, Available: " . ($quantity - $qtyLeft));
        }

        return $totalCostCents;
    }
}
