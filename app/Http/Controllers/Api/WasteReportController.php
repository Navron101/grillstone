<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WasteRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WasteReportController extends Controller
{
    /**
     * Get daily waste summary
     */
    public function daily(Request $request)
    {
        $date = $request->input('date', now()->toDateString());

        $summary = WasteRecord::query()
            ->whereDate('wasted_at', $date)
            ->with('product')
            ->get()
            ->groupBy('product_id')
            ->map(function ($records) {
                $product = $records->first()->product;
                return [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'total_quantity' => $records->sum('quantity'),
                    'total_cost' => $records->sum('cost'),
                    'unit' => $records->first()->unit,
                    'count' => $records->count(),
                ];
            })
            ->values();

        $totalCost = $summary->sum('total_cost');
        $totalItems = $summary->count();

        return response()->json([
            'date' => $date,
            'summary' => $summary,
            'total_cost' => $totalCost,
            'total_items' => $totalItems,
        ]);
    }

    /**
     * Get waste trends over time
     */
    public function trends(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $groupBy = $request->input('group_by', 'day'); // day, week, month

        $query = WasteRecord::query()
            ->whereBetween('wasted_at', [$startDate, $endDate]);

        $dateFormat = match($groupBy) {
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $trends = $query
            ->select(
                DB::raw("DATE_FORMAT(wasted_at, '{$dateFormat}') as period"),
                DB::raw('SUM(cost) as total_cost'),
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('COUNT(*) as waste_count')
            )
            ->groupBy('period')
            ->orderBy('period', 'asc')
            ->get();

        return response()->json([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'group_by' => $groupBy,
            'trends' => $trends,
        ]);
    }

    /**
     * Get most wasted products
     */
    public function topWasted(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $limit = $request->input('limit', 10);

        $topWasted = WasteRecord::query()
            ->whereBetween('wasted_at', [$startDate, $endDate])
            ->select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(cost) as total_cost'),
                DB::raw('COUNT(*) as waste_count')
            )
            ->with('product:id,name,type')
            ->groupBy('product_id')
            ->orderByDesc('total_cost')
            ->limit($limit)
            ->get()
            ->map(function ($record) {
                return [
                    'product_id' => $record->product_id,
                    'product_name' => $record->product->name,
                    'product_type' => $record->product->type,
                    'total_quantity' => $record->total_quantity,
                    'total_cost' => $record->total_cost,
                    'waste_count' => $record->waste_count,
                ];
            });

        return response()->json([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'top_wasted' => $topWasted,
        ]);
    }

    /**
     * Get waste by reason
     */
    public function byReason(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $byReason = WasteRecord::query()
            ->whereBetween('wasted_at', [$startDate, $endDate])
            ->select(
                'reason',
                DB::raw('SUM(cost) as total_cost'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('reason')
            ->orderByDesc('total_cost')
            ->get();

        return response()->json([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'by_reason' => $byReason,
        ]);
    }

    /**
     * Get comprehensive waste summary
     */
    public function summary(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $totalWaste = WasteRecord::query()
            ->whereBetween('wasted_at', [$startDate, $endDate])
            ->select(
                DB::raw('SUM(cost) as total_cost'),
                DB::raw('COUNT(*) as total_records'),
                DB::raw('COUNT(DISTINCT product_id) as unique_products')
            )
            ->first();

        $dailyAverage = WasteRecord::query()
            ->whereBetween('wasted_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(wasted_at) as date'),
                DB::raw('SUM(cost) as daily_cost')
            )
            ->groupBy('date')
            ->get()
            ->avg('daily_cost');

        return response()->json([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_cost' => $totalWaste->total_cost ?? 0,
            'total_records' => $totalWaste->total_records ?? 0,
            'unique_products' => $totalWaste->unique_products ?? 0,
            'daily_average_cost' => round($dailyAverage ?? 0, 2),
        ]);
    }
}
