<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    // GET /api/stock/summary?location_id=1&product_ids=1,2,3
    // or /api/stock/summary?location_id=1&product_ids[]=1&product_ids[]=2
    public function summary(Request $r)
    {
        $loc = (int) $r->query('location_id', 1);

        // Accept BOTH formats for product_ids
        $idsParam = $r->input('product_ids', []);
        if (is_string($idsParam)) {
            $ids = collect(explode(',', $idsParam))->filter()->map('intval')->all();
        } elseif (is_array($idsParam)) {
            $ids = collect($idsParam)->filter()->map('intval')->all();
        } else {
            $ids = [];
        }

        // Sum from lots, alias as on_hand (what the POS expects)
        $q = DB::table('inventory_lots')
            ->select('product_id', DB::raw('COALESCE(SUM(qty_on_hand),0) AS on_hand'))
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
                'p.id AS product_id', 'p.name',
                DB::raw('COALESCE(SUM(il.qty_on_hand),0) AS on_hand'),
                DB::raw('COALESCE(rr.min_qty,0) AS min_qty')
            )
            ->groupBy('p.id', 'p.name', 'rr.min_qty')
            ->havingRaw('on_hand < COALESCE(min_qty,0)')
            ->orderBy('on_hand')
            ->get();

        return response()->json($rows);
    }
}
