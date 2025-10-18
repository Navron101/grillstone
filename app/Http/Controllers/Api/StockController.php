<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    // GET /api/stock/summary?location_id=1&product_ids=1,2,3
    public function summary(Request $r)
    {
        $loc = (int) $r->query('location_id', 1);
        $ids = collect(explode(',', (string) $r->query('product_ids', '')))
            ->filter()->map(fn($v) => (int) $v)->all();

        $q = DB::table('inventory_lots')
            ->select('product_id', DB::raw('SUM(qty_on_hand) as qty'))
            ->where('location_id', $loc)
            ->groupBy('product_id');

        if (!empty($ids)) {
            $q->whereIn('product_id', $ids);
        }

        return response()->json($q->get());
    }

    // GET /api/stock/low?location_id=1
    public function low(Request $r)
    {
        $loc = (int) $r->query('location_id', 1);

        $rows = DB::table('products as p')
            ->leftJoin('reorder_rules as rr', 'rr.product_id', '=', 'p.id')
            ->leftJoin('inventory_lots as il', function ($j) use ($loc) {
                $j->on('il.product_id', '=', 'p.id')
                  ->where('il.location_id', '=', $loc);
            })
            ->select(
                'p.id', 'p.name',
                DB::raw('COALESCE(SUM(il.qty_on_hand),0) as qty'),
                DB::raw('COALESCE(rr.min_qty,0) as min_qty')
            )
            ->groupBy('p.id','p.name','rr.min_qty')
            ->havingRaw('qty < COALESCE(min_qty,0)')
            ->orderBy('qty')
            ->get();

        return response()->json($rows);
    }
}
