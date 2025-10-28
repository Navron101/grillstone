<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stocktake;
use App\Models\StocktakeLine;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\StocktakeVarianceExport;
use Maatwebsite\Excel\Facades\Excel;

class StocktakeController extends Controller
{
    /**
     * Get list of all stocktakes
     */
    public function index(Request $request)
    {
        $query = Stocktake::with(['location', 'counter'])
            ->orderByDesc('created_at');

        if ($locationId = $request->query('location_id')) {
            $query->where('location_id', $locationId);
        }

        $stocktakes = $query->get();

        return response()->json($stocktakes);
    }

    /**
     * Create a new stocktake with current system quantities
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'location_id' => 'required|integer|exists:locations,id',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'product_ids' => 'nullable|array', // Optional: only stocktake specific products
        ]);

        return DB::transaction(function () use ($data, $request) {
            // Create stocktake header
            $stocktake = Stocktake::create([
                'location_id' => $data['location_id'],
                'reference' => $data['reference'] ?? 'ST-' . now()->format('Y-m-d-His'),
                'status' => 'draft',
                'counted_by' => auth()->id() ?? 1, // Default to user 1 if not authenticated
                'counted_at' => now(),
                'notes' => $data['notes'] ?? null,
            ]);

            // Get products to stocktake
            $productQuery = Product::query();
            if (!empty($data['product_ids'])) {
                $productQuery->whereIn('id', $data['product_ids']);
            }
            $products = $productQuery->get();

            // Get current system quantities
            $stockData = DB::table('inventory_lots')
                ->select('product_id', DB::raw('COALESCE(SUM(qty_on_hand),0) AS qty'))
                ->where('location_id', $data['location_id'])
                ->groupBy('product_id')
                ->get()
                ->keyBy('product_id');

            // Get average unit cost for each product
            $costData = DB::table('inventory_lots')
                ->select('product_id', DB::raw('AVG(unit_cost_cents) AS avg_cost'))
                ->where('location_id', $data['location_id'])
                ->where('qty_on_hand', '>', 0)
                ->groupBy('product_id')
                ->get()
                ->keyBy('product_id');

            // Create lines for each product
            foreach ($products as $product) {
                $systemQty = $stockData->get($product->id)?->qty ?? 0;
                $avgCost = $costData->get($product->id)?->avg_cost ?? 0;

                StocktakeLine::create([
                    'stocktake_id' => $stocktake->id,
                    'product_id' => $product->id,
                    'system_qty' => $systemQty,
                    'actual_qty' => null, // To be filled during count
                    'unit_cost_cents' => (int) $avgCost,
                ]);
            }

            return response()->json([
                'stocktake' => $stocktake->load('lines.product'),
            ], 201);
        });
    }

    /**
     * Get a specific stocktake with all lines
     */
    public function show($id)
    {
        $stocktake = Stocktake::with(['lines.product', 'location', 'counter'])
            ->findOrFail($id);

        return response()->json($stocktake);
    }

    /**
     * Update stocktake lines (enter actual counts)
     */
    public function updateLines(Request $request, $id)
    {
        $stocktake = Stocktake::findOrFail($id);

        if ($stocktake->status === 'completed') {
            return response()->json(['error' => 'Cannot update completed stocktake'], 400);
        }

        $data = $request->validate([
            'lines' => 'required|array',
            'lines.*.id' => 'required|integer|exists:stocktake_lines,id',
            'lines.*.actual_qty' => 'nullable|numeric|min:0',
            'lines.*.notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($data, $stocktake) {
            foreach ($data['lines'] as $lineData) {
                $line = StocktakeLine::where('id', $lineData['id'])
                    ->where('stocktake_id', $stocktake->id)
                    ->firstOrFail();

                // If actual_qty is not provided or empty, assume it equals system_qty (no variance)
                if (!isset($lineData['actual_qty']) || $lineData['actual_qty'] === '' || $lineData['actual_qty'] === null) {
                    $lineData['actual_qty'] = $line->system_qty;
                }

                $line->update([
                    'actual_qty' => $lineData['actual_qty'],
                    'notes' => $lineData['notes'] ?? null,
                ]);
                // Variance is calculated automatically by the model
            }
        });

        return response()->json([
            'stocktake' => $stocktake->fresh(['lines.product']),
        ]);
    }

    /**
     * Complete stocktake and apply adjustments
     */
    public function complete(Request $request, $id)
    {
        $stocktake = Stocktake::with('lines')->findOrFail($id);

        if ($stocktake->status === 'completed') {
            return response()->json(['error' => 'Stocktake already completed'], 400);
        }

        $applyAdjustments = $request->input('apply_adjustments', false);

        return DB::transaction(function () use ($stocktake, $applyAdjustments) {
            if ($applyAdjustments) {
                // Apply variance adjustments to inventory
                foreach ($stocktake->lines as $line) {
                    if ($line->variance != 0 && $line->actual_qty !== null) {
                        // Create stock movement for the variance
                        DB::table('stock_movements')->insert([
                            'product_id' => $line->product_id,
                            'location_id' => $stocktake->location_id,
                            'qty_delta' => $line->variance,
                            'reason' => 'variance',
                            'ref_type' => 'stocktake',
                            'ref_id' => $stocktake->id,
                            'unit_cost_cents' => $line->unit_cost_cents,
                            'created_by' => auth()->id() ?? 1,
                            'created_at' => now(),
                        ]);

                        // Adjust inventory lots (simple approach: create adjustment lot)
                        if ($line->variance != 0) {
                            DB::table('inventory_lots')->insert([
                                'product_id' => $line->product_id,
                                'location_id' => $stocktake->location_id,
                                'qty_on_hand' => $line->variance,
                                'unit_cost_cents' => $line->unit_cost_cents,
                                'lot_code' => 'STOCKTAKE-ADJ-' . $stocktake->id,
                                'received_at' => now(),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }

            $stocktake->update([
                'status' => 'completed',
                'counted_at' => now(),
            ]);

            return response()->json([
                'stocktake' => $stocktake->fresh(['lines.product']),
                'adjustments_applied' => $applyAdjustments,
            ]);
        });
    }

    /**
     * Cancel a stocktake
     */
    public function cancel($id)
    {
        $stocktake = Stocktake::findOrFail($id);

        if ($stocktake->status === 'completed') {
            return response()->json(['error' => 'Cannot cancel completed stocktake'], 400);
        }

        $stocktake->update(['status' => 'cancelled']);

        return response()->json(['stocktake' => $stocktake]);
    }

    /**
     * Delete a stocktake (only drafts/cancelled)
     */
    public function destroy($id)
    {
        $stocktake = Stocktake::findOrFail($id);

        if ($stocktake->status === 'completed') {
            return response()->json(['error' => 'Cannot delete completed stocktake'], 400);
        }

        $stocktake->delete();

        return response()->json(['message' => 'Stocktake deleted']);
    }

    /**
     * Get variance report for a stocktake
     */
    public function varianceReport($id)
    {
        $stocktake = Stocktake::with(['lines.product', 'location', 'counter'])
            ->findOrFail($id);

        // Calculate summary stats
        $totalItems = $stocktake->lines->count();
        $countedItems = $stocktake->lines->filter(fn($l) => $l->actual_qty !== null)->count();
        $itemsWithVariance = $stocktake->lines->filter(fn($l) => $l->variance != 0)->count();

        $totalVarianceValue = $stocktake->lines->reduce(function ($sum, $line) {
            if ($line->variance !== null && $line->variance !== 0) {
                return $sum + ($line->variance * ($line->unit_cost_cents ?? 0) / 100);
            }
            return $sum;
        }, 0);

        // Group variances
        $positiveVariances = $stocktake->lines->filter(fn($l) => $l->variance > 0);
        $negativeVariances = $stocktake->lines->filter(fn($l) => $l->variance < 0);

        return response()->json([
            'stocktake' => $stocktake,
            'summary' => [
                'total_items' => $totalItems,
                'counted_items' => $countedItems,
                'items_with_variance' => $itemsWithVariance,
                'total_variance_value' => $totalVarianceValue,
                'positive_variances_count' => $positiveVariances->count(),
                'negative_variances_count' => $negativeVariances->count(),
                'positive_variances_value' => $positiveVariances->sum(fn($l) => $l->variance * ($l->unit_cost_cents ?? 0) / 100),
                'negative_variances_value' => $negativeVariances->sum(fn($l) => $l->variance * ($l->unit_cost_cents ?? 0) / 100),
            ],
            'variances' => $stocktake->lines->filter(fn($l) => $l->variance != 0)->values(),
        ]);
    }

    /**
     * Download variance report as Excel
     */
    public function downloadVarianceReport($id)
    {
        $stocktake = Stocktake::findOrFail($id);
        $filename = 'stocktake-variance-' . $stocktake->reference . '-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new StocktakeVarianceExport($id), $filename);
    }
}
