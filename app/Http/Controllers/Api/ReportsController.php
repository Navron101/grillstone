<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportsController extends Controller
{
    /**
     * Get dashboard overview with key metrics
     */
    public function dashboard(Request $request)
    {
        $locationId = (int) $request->query('location_id', 1);

        // Support both days-based and custom date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->query('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->query('end_date'))->endOfDay();
        } else {
            $days = (int) $request->query('days', 30);
            $startDate = now()->subDays($days)->startOfDay();
            $endDate = now()->endOfDay();
        }

        // Sales Overview
        $salesData = DB::table('orders')
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->where('status', '!=', 'cancelled')
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(total_cents) as total_revenue_cents,
                SUM(subtotal_cents) as total_subtotal_cents,
                SUM(tax_cents) as total_tax_cents,
                SUM(discount_cents) as total_discount_cents,
                AVG(total_cents) as avg_order_cents
            ')
            ->first();

        // Extract COGS from order meta
        // Try SQL-based extraction first for better performance
        try {
            $totalCogs = DB::table('orders')
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->where('status', '!=', 'cancelled')
                ->whereNotNull('meta')
                ->selectRaw('SUM(COALESCE(JSON_EXTRACT(meta, "$.cogs_cents"), 0)) as total_cogs')
                ->value('total_cogs') ?? 0;
        } catch (\Exception $e) {
            // Fallback to PHP-based extraction if SQL JSON functions not available
            $totalCogs = DB::table('orders')
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->where('status', '!=', 'cancelled')
                ->get()
                ->sum(function ($order) {
                    if (!$order->meta) return 0;
                    $meta = is_string($order->meta) ? json_decode($order->meta, true) : (array) $order->meta;
                    return $meta['cogs_cents'] ?? 0;
                });
        }

        // Sales by Day (last N days)
        $dailySales = DB::table('orders')
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->where('status', '!=', 'cancelled')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders, SUM(total_cents) as revenue_cents')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top Selling Products
        $topSellers = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.created_at', '>=', $startDate)
            ->where('orders.created_at', '<=', $endDate)
            ->where('orders.status', '!=', 'cancelled')
            ->selectRaw('
                products.id,
                products.name,
                products.category,
                SUM(order_items.qty) as total_qty,
                SUM(order_items.line_total_cents) as total_revenue_cents
            ')
            ->groupBy('products.id', 'products.name', 'products.category')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        // Sales by Category
        $categoryBreakdown = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.created_at', '>=', $startDate)
            ->where('orders.created_at', '<=', $endDate)
            ->where('orders.status', '!=', 'cancelled')
            ->selectRaw('
                COALESCE(products.category, "Other") as category,
                SUM(order_items.qty) as total_qty,
                SUM(order_items.line_total_cents) as total_revenue_cents
            ')
            ->groupBy('category')
            ->orderByDesc('total_revenue_cents')
            ->get();

        // Low Stock Items
        $lowStock = DB::table('products')
            ->leftJoin('inventory_lots', function ($join) use ($locationId) {
                $join->on('products.id', '=', 'inventory_lots.product_id')
                    ->where('inventory_lots.location_id', '=', $locationId);
            })
            ->where('products.type', '=', 'ingredient')
            ->selectRaw('
                products.id,
                products.name,
                products.unit_name,
                COALESCE(SUM(inventory_lots.qty_on_hand), 0) as on_hand,
                products.low_stock_threshold
            ')
            ->groupBy('products.id', 'products.name', 'products.unit_name', 'products.low_stock_threshold')
            ->havingRaw('on_hand <= COALESCE(products.low_stock_threshold, 5)')
            ->orderBy('on_hand')
            ->limit(20)
            ->get();

        // Recent Stocktake Variances
        $recentVariances = DB::table('stocktakes')
            ->join('stocktake_lines', 'stocktakes.id', '=', 'stocktake_lines.stocktake_id')
            ->join('products', 'stocktake_lines.product_id', '=', 'products.id')
            ->where('stocktakes.status', 'completed')
            ->where('stocktakes.location_id', $locationId)
            ->whereRaw('ABS(stocktake_lines.variance) > 0.01')
            ->selectRaw('
                stocktakes.reference,
                stocktakes.counted_at,
                products.name as product_name,
                stocktake_lines.system_qty,
                stocktake_lines.actual_qty,
                stocktake_lines.variance
            ')
            ->orderByDesc('stocktakes.counted_at')
            ->limit(20)
            ->get();

        return response()->json([
            'overview' => [
                'total_orders' => (int) ($salesData->total_orders ?? 0),
                'total_revenue' => (float) (($salesData->total_revenue_cents ?? 0) / 100),
                'total_cogs' => (float) ($totalCogs / 100),
                'gross_profit' => (float) ((($salesData->total_revenue_cents ?? 0) - $totalCogs) / 100),
                'avg_order_value' => (float) (($salesData->avg_order_cents ?? 0) / 100),
                'total_tax' => (float) (($salesData->total_tax_cents ?? 0) / 100),
                'total_discount' => (float) (($salesData->total_discount_cents ?? 0) / 100),
            ],
            'daily_sales' => $dailySales->map(fn($d) => [
                'date' => $d->date,
                'orders' => (int) $d->orders,
                'revenue' => (float) ($d->revenue_cents / 100),
            ]),
            'top_sellers' => $topSellers->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'category' => $p->category ?? 'Other',
                'qty_sold' => (int) $p->total_qty,
                'revenue' => (float) ($p->total_revenue_cents / 100),
            ]),
            'category_breakdown' => $categoryBreakdown->map(fn($c) => [
                'category' => $c->category,
                'qty_sold' => (int) $c->total_qty,
                'revenue' => (float) ($c->total_revenue_cents / 100),
            ]),
            'low_stock' => $lowStock->map(fn($i) => [
                'id' => $i->id,
                'name' => $i->name,
                'unit' => $i->unit_name ?? 'units',
                'on_hand' => (float) $i->on_hand,
                'threshold' => (float) ($i->low_stock_threshold ?? 5),
            ]),
            'recent_variances' => $recentVariances->map(fn($v) => [
                'stocktake' => $v->reference ?? 'N/A',
                'counted_at' => $v->counted_at,
                'product' => $v->product_name,
                'system_qty' => (float) $v->system_qty,
                'actual_qty' => (float) $v->actual_qty,
                'variance' => (float) $v->variance,
            ]),
        ]);
    }

    /**
     * Get sales trends over time with grouping options
     */
    public function salesTrends(Request $request)
    {
        $period = $request->query('period', 'daily'); // daily, weekly, monthly
        $days = (int) $request->query('days', 30);
        $startDate = now()->subDays($days)->startOfDay();

        $groupFormat = match($period) {
            'weekly' => '%Y-%u',
            'monthly' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $trends = DB::table('orders')
            ->where('created_at', '>=', $startDate)
            ->where('status', '!=', 'cancelled')
            ->selectRaw("
                DATE_FORMAT(created_at, '{$groupFormat}') as period,
                COUNT(*) as orders,
                SUM(total_cents) as revenue_cents,
                AVG(total_cents) as avg_order_cents
            ")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return response()->json([
            'period' => $period,
            'trends' => $trends->map(fn($t) => [
                'period' => $t->period,
                'orders' => (int) $t->orders,
                'revenue' => (float) ($t->revenue_cents / 100),
                'avg_order' => (float) ($t->avg_order_cents / 100),
            ]),
        ]);
    }
}
