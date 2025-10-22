<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TillSettlementController extends Controller
{
    /**
     * Calculate expected amounts for settlement
     */
    public function calculate(Request $request)
    {
        // Get last settlement to determine period start
        $last = DB::table('till_settlements')
            ->orderBy('period_end', 'desc')
            ->first();

        $periodStart = $last ? $last->period_end : today()->startOfDay();
        $periodEnd = now();

        // Get all payments in the period
        $payments = DB::table('payments')
            ->where('created_at', '>=', $periodStart)
            ->where('created_at', '<=', $periodEnd)
            ->get();

        // Calculate totals by payment method
        $cashCents = 0;
        $cardCents = 0;
        $giftCardCents = 0;

        foreach ($payments as $payment) {
            if ($payment->method === 'cash' || $payment->method === 'wallet') {
                $cashCents += $payment->amount_cents;
            } elseif ($payment->method === 'card') {
                $cardCents += $payment->amount_cents;
            } elseif ($payment->method === 'gift_card') {
                $giftCardCents += $payment->amount_cents;
            }
        }

        // Get payouts
        $payoutsCents = DB::table('payouts')
            ->where('payout_date', '>=', $periodStart)
            ->where('payout_date', '<=', $periodEnd)
            ->where('status', 'completed')
            ->sum('amount_cents');

        // Get paid-in amounts (if tracked)
        $paidInCents = 0;

        // Calculate net cash
        $netCashCents = $cashCents - $payoutsCents + $paidInCents;

        // Get number of transactions
        $numTransactions = DB::table('orders')
            ->where('created_at', '>=', $periodStart)
            ->where('created_at', '<=', $periodEnd)
            ->count();

        // Total sales
        $totalSalesCents = $cashCents + $cardCents + $giftCardCents;

        // Calculate COGS - simple version
        $cogsCents = 0; // Will be calculated if you have FIFO tracking

        $profitCents = $totalSalesCents - $cogsCents;

        // Get credit card transactions detail
        $cardTransactions = DB::table('payments as p')
            ->join('orders as o', 'o.id', '=', 'p.order_id')
            ->where('p.method', 'card')
            ->where('p.created_at', '>=', $periodStart)
            ->where('p.created_at', '<=', $periodEnd)
            ->select([
                'p.id',
                'p.created_at',
                'p.amount_cents',
                'p.reference',
                'o.id as order_id',
            ])
            ->orderBy('p.created_at', 'asc')
            ->get();

        return response()->json([
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'expected_cash_cents' => $cashCents,
            'expected_card_cents' => $cardCents,
            'expected_gift_card_cents' => $giftCardCents,
            'total_sales_cents' => $totalSalesCents,
            'num_transactions' => $numTransactions,
            'cogs_cents' => $cogsCents,
            'profit_cents' => $profitCents,
            'paid_out_cents' => $payoutsCents,
            'paid_in_cents' => $paidInCents,
            'net_cash_cents' => $netCashCents,
            'net_card_cents' => $cardCents,
            'card_transactions' => $cardTransactions,
        ]);
    }

    /**
     * Create a settlement
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'actual_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get calculated data
        $calculated = $this->calculate($request)->getData();

        $actualCashCents = round($request->actual_cash * 100);
        $expectedCashCents = $calculated->expected_cash_cents;
        $varianceCents = $actualCashCents - $expectedCashCents;

        $id = DB::table('till_settlements')->insertGetId([
            'user_id' => 1, // TODO: Get from auth
            'settlement_date' => now(),
            'period_start' => \Carbon\Carbon::parse($calculated->period_start),
            'period_end' => \Carbon\Carbon::parse($calculated->period_end),
            'expected_cash_cents' => $expectedCashCents,
            'expected_card_cents' => $calculated->expected_card_cents,
            'expected_gift_card_cents' => $calculated->expected_gift_card_cents,
            'actual_cash_cents' => $actualCashCents,
            'cash_variance_cents' => $varianceCents,
            'total_sales_cents' => $calculated->total_sales_cents,
            'num_transactions' => $calculated->num_transactions,
            'cogs_cents' => $calculated->cogs_cents,
            'profit_cents' => $calculated->profit_cents,
            'paid_in_cents' => $calculated->paid_in_cents,
            'paid_out_cents' => $calculated->paid_out_cents,
            'net_cash_cents' => $calculated->net_cash_cents,
            'net_card_cents' => $calculated->net_card_cents,
            'status' => 'completed',
            'notes' => $request->notes,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $settlement = DB::table('till_settlements')->where('id', $id)->first();

        return response()->json($settlement, 201);
    }

    /**
     * Get settlement details
     */
    public function show($id)
    {
        $settlement = DB::table('till_settlements as ts')
            ->leftJoin('users as u', 'u.id', '=', 'ts.user_id')
            ->where('ts.id', $id)
            ->select([
                'ts.*',
                'u.name as user_name'
            ])
            ->first();

        if (!$settlement) {
            return response()->json(['error' => 'Settlement not found'], 404);
        }

        // Get card transactions for this period
        $cardTransactions = DB::table('payments as p')
            ->join('orders as o', 'o.id', '=', 'p.order_id')
            ->where('p.method', 'card')
            ->where('p.created_at', '>=', $settlement->period_start)
            ->where('p.created_at', '<=', $settlement->period_end)
            ->select([
                'p.id',
                'p.created_at',
                'p.amount_cents',
                'p.reference',
                'o.id as order_id',
            ])
            ->orderBy('p.created_at', 'asc')
            ->get();

        return response()->json([
            'settlement' => $settlement,
            'card_transactions' => $cardTransactions,
        ]);
    }

    /**
     * List all settlements
     */
    public function index(Request $request)
    {
        $query = DB::table('till_settlements as ts')
            ->leftJoin('users as u', 'u.id', '=', 'ts.user_id')
            ->select([
                'ts.*',
                'u.name as user_name'
            ]);

        // Filter by date range if provided
        if ($request->has('start_date')) {
            $query->where('ts.settlement_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            // Add 1 day to end_date to include the entire end day
            $endDate = \Carbon\Carbon::parse($request->end_date)->endOfDay();
            $query->where('ts.settlement_date', '<=', $endDate);
        }

        // Filter by specific date if provided
        if ($request->has('date')) {
            $date = \Carbon\Carbon::parse($request->date);
            $query->whereDate('ts.settlement_date', $date->toDateString());
        }

        $settlements = $query->orderBy('ts.settlement_date', 'desc')->get();

        return response()->json($settlements);
    }
}
