# Till Settlement & Payouts System - Implementation Guide

## Overview

This system provides complete till management including:
1. **Payouts** - Track money paid out from the till
2. **Till Settlement** - End-of-day cash reconciliation with variance tracking
3. **Settlement Reports** - Comprehensive reports with cash/card breakdown, COGS, profit
4. **Credit Card Listing** - Detailed card transactions for bank settlement

---

## Database Schema

### `payouts` Table
Tracks money paid out from the till (supplier payments, petty cash, etc.)

```sql
CREATE TABLE payouts (
    id BIGINT PRIMARY KEY,
    user_id BIGINT,              -- Who processed the payout
    amount_cents INT,            -- Amount in cents
    reason VARCHAR,              -- Why (e.g., "Supplier payment")
    recipient VARCHAR,           -- Who received it
    status ENUM,                 -- pending, approved, completed, cancelled
    notes TEXT,
    payout_date TIMESTAMP,
    approved_by BIGINT,          -- Who approved it
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### `till_settlements` Table
Records of till settlements with variance tracking

```sql
CREATE TABLE till_settlements (
    id BIGINT PRIMARY KEY,
    user_id BIGINT,                    -- Who performed settlement
    settlement_date TIMESTAMP,
    period_start TIMESTAMP,            -- Start of period
    period_end TIMESTAMP,              -- End of period

    -- System calculated (expected)
    expected_cash_cents INT,           -- What system expects
    expected_card_cents INT,
    expected_gift_card_cents INT,

    -- Cashier entered (actual)
    actual_cash_cents INT,             -- What cashier counted

    -- Variance
    cash_variance_cents INT,           -- actual - expected

    -- Sales breakdown
    total_sales_cents INT,
    num_transactions INT,
    cogs_cents INT,                    -- Cost of goods sold
    profit_cents INT,                  -- sales - cogs

    -- Paid in/out
    paid_in_cents INT,
    paid_out_cents INT,

    -- Net amounts
    net_cash_cents INT,
    net_card_cents INT,

    status ENUM,                       -- draft, completed, reviewed
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## API Implementation

### PayoutController.php

Location: `app/Http/Controllers/Api/PayoutController.php`

```php
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
            ->leftJoin('users as approver', 'approver.id', '=', 'p.approved_by')
            ->select([
                'p.*',
                'u.name as user_name',
                'approver.name as approver_name'
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
            'payout_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $id = DB::table('payouts')->insertGetId([
            'user_id' => $request->user()->id ?? null,
            'amount_cents' => round($request->amount * 100),
            'reason' => $request->reason,
            'recipient' => $request->recipient,
            'status' => 'completed',
            'notes' => $request->notes,
            'payout_date' => $request->payout_date ?? now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payout = DB::table('payouts')->where('id', $id)->first();

        return response()->json($payout, 201);
    }

    /**
     * Update a payout
     */
    public function update(Request $request, $id)
    {
        $payout = DB::table('payouts')->where('id', $id)->first();
        if (!$payout) {
            return response()->json(['error' => 'Payout not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'sometimes|numeric|min:0',
            'reason' => 'sometimes|string|max:255',
            'recipient' => 'nullable|string|max:255',
            'status' => 'sometimes|in:pending,approved,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $updateData = ['updated_at' => now()];

        if ($request->has('amount')) {
            $updateData['amount_cents'] = round($request->amount * 100);
        }
        if ($request->has('reason')) {
            $updateData['reason'] = $request->reason;
        }
        if ($request->has('recipient')) {
            $updateData['recipient'] = $request->recipient;
        }
        if ($request->has('status')) {
            $updateData['status'] = $request->status;
        }
        if ($request->has('notes')) {
            $updateData['notes'] = $request->notes;
        }

        DB::table('payouts')->where('id', $id)->update($updateData);
        $updated = DB::table('payouts')->where('id', $id)->first();

        return response()->json($updated);
    }

    /**
     * Delete a payout
     */
    public function destroy($id)
    {
        $payout = DB::table('payouts')->where('id', $id)->first();
        if (!$payout) {
            return response()->json(['error' => 'Payout not found'], 404);
        }

        DB::table('payouts')->where('id', $id)->delete();

        return response()->json(['message' => 'Payout deleted successfully']);
    }

    /**
     * Get total payouts for a date range
     */
    public function total(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = DB::table('payouts')
            ->where('status', 'completed');

        if ($startDate) {
            $query->where('payout_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('payout_date', '<=', $endDate);
        }

        $total = $query->sum('amount_cents');

        return response()->json([
            'total_cents' => $total,
            'total_amount' => $total / 100,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }
}
```

### TillSettlementController.php

Location: `app/Http/Controllers/Api/TillSettlementController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TillSettlementController extends Controller
{
    /**
     * List all settlements
     */
    public function index(Request $request)
    {
        $settlements = DB::table('till_settlements as ts')
            ->leftJoin('users as u', 'u.id', '=', 'ts.user_id')
            ->select([
                'ts.*',
                'u.name as user_name'
            ])
            ->orderBy('ts.settlement_date', 'desc')
            ->get();

        return response()->json($settlements);
    }

    /**
     * Get last settlement date/time
     */
    public function lastSettlement()
    {
        $last = DB::table('till_settlements')
            ->orderBy('period_end', 'desc')
            ->first();

        if (!$last) {
            return response()->json([
                'last_settlement' => null,
                'period_start' => null,
            ]);
        }

        return response()->json([
            'last_settlement' => $last->settlement_date,
            'period_start' => $last->period_end,
        ]);
    }

    /**
     * Calculate expected amounts for settlement
     */
    public function calculate(Request $request)
    {
        $periodStart = $request->query('period_start');

        // If no start provided, use last settlement end time or beginning of today
        if (!$periodStart) {
            $last = DB::table('till_settlements')
                ->orderBy('period_end', 'desc')
                ->first();

            $periodStart = $last ? $last->period_end : now()->startOfDay();
        }

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
            if ($payment->payment_method === 'cash') {
                $cashCents += $payment->amount_cents;
            } elseif ($payment->payment_method === 'card') {
                $cardCents += $payment->amount_cents;
            } elseif ($payment->payment_method === 'gift_card') {
                $giftCardCents += $payment->amount_cents;
            }
        }

        // Get payouts
        $payoutsCents = DB::table('payouts')
            ->where('payout_date', '>=', $periodStart)
            ->where('payout_date', '<=', $periodEnd)
            ->where('status', 'completed')
            ->sum('amount_cents');

        // Get paid-in amounts (if you track this)
        $paidInCents = 0; // Implement if you have a paid-in tracking system

        // Calculate net cash (cash sales - payouts + paid-in)
        $netCashCents = $cashCents - $payoutsCents + $paidInCents;

        // Get number of transactions
        $numTransactions = DB::table('orders')
            ->where('created_at', '>=', $periodStart)
            ->where('created_at', '<=', $periodEnd)
            ->count();

        // Get total sales
        $totalSalesCents = $cashCents + $cardCents + $giftCardCents;

        // Calculate COGS (Cost of Goods Sold)
        // This queries the stock_movements table for recipe consumption
        $cogsCents = DB::table('stock_movements as sm')
            ->join('inventory_lots as lot', 'lot.id', '=', 'sm.lot_id')
            ->where('sm.reason', 'sale')
            ->where('sm.created_at', '>=', $periodStart)
            ->where('sm.created_at', '<=', $periodEnd)
            ->selectRaw('SUM(ABS(sm.quantity) * lot.unit_cost_cents) as total_cogs')
            ->value('total_cogs') ?? 0;

        $profitCents = $totalSalesCents - $cogsCents;

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
            'net_card_cents' => $cardCents, // Cards don't have paid in/out
        ]);
    }

    /**
     * Create a settlement
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'actual_cash' => 'required|numeric|min:0',
            'period_start' => 'nullable|date',
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
            'user_id' => $request->user()->id ?? null,
            'settlement_date' => now(),
            'period_start' => $calculated->period_start,
            'period_end' => $calculated->period_end,
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

        return response()->json($settlement);
    }

    /**
     * Get credit card transactions for bank settlement
     */
    public function creditCardListing(Request $request)
    {
        $settlementId = $request->query('settlement_id');

        if ($settlementId) {
            $settlement = DB::table('till_settlements')->where('id', $settlementId)->first();
            if (!$settlement) {
                return response()->json(['error' => 'Settlement not found'], 404);
            }
            $periodStart = $settlement->period_start;
            $periodEnd = $settlement->period_end;
        } else {
            $periodStart = $request->query('start_date');
            $periodEnd = $request->query('end_date', now());
        }

        $cardTransactions = DB::table('payments as p')
            ->join('orders as o', 'o.id', '=', 'p.order_id')
            ->where('p.payment_method', 'card')
            ->where('p.created_at', '>=', $periodStart)
            ->where('p.created_at', '<=', $periodEnd)
            ->select([
                'p.id',
                'p.created_at as transaction_time',
                'p.amount_cents',
                'p.reference',
                'o.id as order_number',
            ])
            ->orderBy('p.created_at', 'asc')
            ->get();

        $totalCents = $cardTransactions->sum('amount_cents');

        return response()->json([
            'transactions' => $cardTransactions,
            'count' => $cardTransactions->count(),
            'total_cents' => $totalCents,
            'total_amount' => $totalCents / 100,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
        ]);
    }
}
```

---

## API Routes

Add to `routes/api.php`:

```php
use App\Http\Controllers\Api\PayoutController;
use App\Http\Controllers\Api\TillSettlementController;

// Payouts
Route::get('/payouts', [PayoutController::class, 'index']);
Route::post('/payouts', [PayoutController::class, 'store']);
Route::put('/payouts/{id}', [PayoutController::class, 'update']);
Route::delete('/payouts/{id}', [PayoutController::class, 'destroy']);
Route::get('/payouts/total', [PayoutController::class, 'total']);

// Till Settlements
Route::get('/settlements', [TillSettlementController::class, 'index']);
Route::get('/settlements/last', [TillSettlementController::class, 'lastSettlement']);
Route::get('/settlements/calculate', [TillSettlementController::class, 'calculate']);
Route::post('/settlements', [TillSettlementController::class, 'store']);
Route::get('/settlements/{id}', [TillSettlementController::class, 'show']);
Route::get('/settlements/credit-cards', [TillSettlementController::class, 'creditCardListing']);
```

---

## Example API Usage

### 1. Record a Payout

```bash
curl -X POST http://localhost:8000/api/payouts \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 5000,
    "reason": "Supplier payment - Fresh produce",
    "recipient": "Kingston Market",
    "notes": "Weekly vegetable order"
  }'
```

### 2. Calculate Expected Amounts for Settlement

```bash
curl http://localhost:8000/api/settlements/calculate
```

Response:
```json
{
  "period_start": "2025-10-21 06:00:00",
  "period_end": "2025-10-21 18:30:00",
  "expected_cash_cents": 45000000,
  "expected_card_cents": 23000000,
  "expected_gift_card_cents": 500000,
  "total_sales_cents": 68500000,
  "num_transactions": 127,
  "cogs_cents": 25000000,
  "profit_cents": 43500000,
  "paid_out_cents": 5000000,
  "paid_in_cents": 0,
  "net_cash_cents": 40000000,
  "net_card_cents": 23000000
}
```

### 3. Perform Till Settlement

Cashier counts JMD 398,500.00 in the till:

```bash
curl -X POST http://localhost:8000/api/settlements \
  -H "Content-Type: application/json" \
  -d '{
    "actual_cash": 398500,
    "notes": "End of day settlement - Monday"
  }'
```

Response shows variance:
```json
{
  "id": 1,
  "expected_cash_cents": 40000000,
  "actual_cash_cents": 39850000,
  "cash_variance_cents": -150000,
  "total_sales_cents": 68500000,
  "profit_cents": 43500000,
  ...
}
```

Variance: -JMD 1,500.00 (short)

### 4. Get Credit Card Listing for Bank

```bash
curl http://localhost:8000/api/settlements/credit-cards?settlement_id=1
```

Response:
```json
{
  "transactions": [
    {
      "id": 45,
      "transaction_time": "2025-10-21 09:15:00",
      "amount_cents": 350000,
      "reference": "VISA-xxxx1234",
      "order_number": 101
    },
    ...
  ],
  "count": 48,
  "total_cents": 23000000,
  "total_amount": 230000.00
}
```

---

## Settlement Report Structure

The settlement report should include:

### 1. Header
- Settlement Date/Time
- Period (Start - End)
- Performed By

### 2. Cash Breakdown
```
CASH TRANSACTIONS
──────────────────────────────────
Cash Sales:              JMD 450,000.00
Paid Out:               -JMD  50,000.00
Paid In:                +JMD       0.00
──────────────────────────────────
Net Cash Expected:       JMD 400,000.00
Cash Counted (Actual):   JMD 398,500.00
──────────────────────────────────
VARIANCE:               -JMD   1,500.00 (SHORT)
```

### 3. Card Breakdown
```
CREDIT/DEBIT CARDS
──────────────────────────────────
Total Card Sales:        JMD 230,000.00
Number of Transactions:  48
Average Transaction:     JMD   4,791.67
```

### 4. Gift Cards
```
GIFT CARDS
──────────────────────────────────
Total Gift Card Sales:   JMD   5,000.00
Number of Transactions:  2
```

### 5. Sales Summary
```
SALES SUMMARY
──────────────────────────────────
Total Sales:             JMD 685,000.00
Number of Transactions:  127
Average Sale:            JMD   5,393.70
Cost of Goods Sold:      JMD 250,000.00
──────────────────────────────────
GROSS PROFIT:            JMD 435,000.00
Profit Margin:           63.5%
```

### 6. Payment Method Breakdown
```
PAYMENT METHOD BREAKDOWN
──────────────────────────────────
Cash:           JMD 450,000.00  (65.7%)
Credit Card:    JMD 230,000.00  (33.6%)
Gift Card:      JMD   5,000.00  ( 0.7%)
──────────────────────────────────
TOTAL:          JMD 685,000.00
```

### 7. Credit Card Detail (For Bank)
```
CREDIT CARD TRANSACTIONS DETAIL
──────────────────────────────────
Time      Order#  Reference        Amount
09:15 AM  #101    VISA-1234   JMD 3,500.00
09:32 AM  #102    MC-5678     JMD 6,200.00
...
──────────────────────────────────
TOTAL: 48 transactions, JMD 230,000.00
```

---

## Testing the System

### Complete Settlement Flow:

1. **Record some sales during the day**
2. **Record payouts if any:**
   ```bash
   POST /api/payouts
   {
     "amount": 3000,
     "reason": "Petty cash - Office supplies"
   }
   ```

3. **At end of day, calculate expected:**
   ```bash
   GET /api/settlements/calculate
   ```

4. **Cashier counts cash:**
   - System says: JMD 45,000.00 expected
   - Cashier counts: JMD 44,800.00

5. **Submit settlement:**
   ```bash
   POST /api/settlements
   {
     "actual_cash": 44800
   }
   ```

6. **System calculates variance:**
   - Variance: -JMD 200.00 (short)

7. **Generate reports:**
   - Settlement summary
   - Credit card listing for bank

---

## Summary

✅ Migrations created for payouts and settlements
✅ PayoutController - Full CRUD for cash payouts
✅ TillSettlementController - Settlement with variance calculation
✅ Credit card listing for bank reconciliation
✅ COGS and profit calculation
✅ Comprehensive settlement data tracking

Next steps: Create the UI pages and PDF report template!
