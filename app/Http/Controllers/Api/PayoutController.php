<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PayoutController extends Controller
{
    /**
     * List all payouts
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = DB::table('payouts as p')
            ->leftJoin('users as u', 'u.id', '=', 'p.user_id')
            ->select([
                'p.*',
                'u.name as user_name'
            ])
            ->orderBy('p.payout_date', 'desc');

        if ($status) {
            $query->where('p.status', $status);
        }

        if ($startDate) {
            $query->where('p.payout_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('p.payout_date', '<=', $endDate);
        }

        $payouts = $query->get();

        return response()->json($payouts);
    }

    /**
     * Create a new payout
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'reason' => 'required|string|max:255',
            'recipient' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $id = DB::table('payouts')->insertGetId([
            'user_id' => 1, // TODO: Get from auth
            'amount_cents' => round($request->amount * 100),
            'reason' => $request->reason,
            'recipient' => $request->recipient,
            'status' => 'completed',
            'notes' => $request->notes,
            'payout_date' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payout = DB::table('payouts')->where('id', $id)->first();

        return response()->json($payout, 201);
    }

    /**
     * Get total payouts for today
     */
    public function todayTotal()
    {
        $total = DB::table('payouts')
            ->whereDate('payout_date', today())
            ->where('status', 'completed')
            ->sum('amount_cents');

        return response()->json([
            'total_cents' => $total,
            'total_amount' => $total / 100,
        ]);
    }
}
