# How to Close the Till and Process Payouts - Quick Guide

## Overview

This guide shows you exactly how to:
1. **Process Payouts** - Pay out cash from the till
2. **Close the Till** - End-of-day settlement with cash count
3. **View the Settlement Report** - See variance, sales, COGS, profit, and card listing

---

## 1. Process a Payout (Paid Out)

**When to use:** When you pay cash out of the till (supplier payment, petty cash, etc.)

### Using API:

```bash
curl -X POST http://localhost:8000/api/payouts \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 5000,
    "reason": "Supplier payment - Kingston Market",
    "recipient": "Kingston Market",
    "notes": "Weekly vegetable order"
  }'
```

### Example Response:
```json
{
  "id": 1,
  "amount_cents": 500000,
  "reason": "Supplier payment - Kingston Market",
  "recipient": "Kingston Market",
  "status": "completed",
  "payout_date": "2025-10-21 14:30:00"
}
```

---

## 2. Close the Till (Settlement)

### Step 1: Check Expected Cash

**First, see what the system expects:**

```bash
curl http://localhost:8000/api/settlements/calculate
```

### Example Response:
```json
{
  "period_start": "2025-10-21 06:00:00",
  "period_end": "2025-10-21 18:00:00",
  "expected_cash_cents": 45000000,
  "expected_card_cents": 23000000,
  "expected_gift_card_cents": 500000,
  "total_sales_cents": 68500000,
  "num_transactions": 127,
  "cogs_cents": 0,
  "profit_cents": 68500000,
  "paid_out_cents": 5000000,
  "paid_in_cents": 0,
  "net_cash_cents": 40000000,
  "net_card_cents": 23000000,
  "card_transactions": [...]
}
```

**What this means:**
- **Expected Cash:** JMD 450,000.00 (cash sales)
- **Paid Out:** JMD 50,000.00 (payouts you made)
- **Net Cash Expected:** JMD 400,000.00 (cash - payouts)
- **Card Sales:** JMD 230,000.00
- **Total Sales:** JMD 685,000.00
- **Transactions:** 127 sales

### Step 2: Count the Cash

The cashier physically counts the money in the till.

**Example:** Cashier counts JMD 398,500.00

### Step 3: Submit the Settlement

```bash
curl -X POST http://localhost:8000/api/settlements \
  -H "Content-Type: application/json" \
  -d '{
    "actual_cash": 398500,
    "notes": "End of day settlement - Monday"
  }'
```

### Example Response:
```json
{
  "id": 1,
  "settlement_date": "2025-10-21 18:00:00",
  "period_start": "2025-10-21 06:00:00",
  "period_end": "2025-10-21 18:00:00",

  "expected_cash_cents": 40000000,
  "actual_cash_cents": 39850000,
  "cash_variance_cents": -150000,

  "expected_card_cents": 23000000,
  "expected_gift_card_cents": 500000,

  "total_sales_cents": 68500000,
  "num_transactions": 127,
  "cogs_cents": 0,
  "profit_cents": 68500000,

  "paid_out_cents": 5000000,
  "paid_in_cents": 0,

  "net_cash_cents": 40000000,
  "net_card_cents": 23000000,

  "status": "completed"
}
```

**Variance Explanation:**
- Expected: JMD 400,000.00
- Actual: JMD 398,500.00
- **Variance: -JMD 1,500.00 (SHORT)**

This means you're **short** JMD 1,500.00

---

## 3. View Settlement Report

### Get Full Settlement Details:

```bash
curl http://localhost:8000/api/settlements/1
```

### Example Response:
```json
{
  "settlement": {
    "id": 1,
    "settlement_date": "2025-10-21 18:00:00",
    "expected_cash_cents": 40000000,
    "actual_cash_cents": 39850000,
    "cash_variance_cents": -150000,
    "expected_card_cents": 23000000,
    "total_sales_cents": 68500000,
    "num_transactions": 127,
    "profit_cents": 68500000,
    "user_name": "Admin User"
  },
  "card_transactions": [
    {
      "id": 45,
      "created_at": "2025-10-21 09:15:23",
      "amount_cents": 350000,
      "reference": "VISA-1234",
      "order_id": 101
    },
    {
      "id": 46,
      "created_at": "2025-10-21 09:32:15",
      "amount_cents": 620000,
      "reference": "MC-5678",
      "order_id": 102
    }
    // ... 46 more transactions
  ]
}
```

---

## Settlement Report Breakdown

Here's what the report shows:

### 📊 CASH BREAKDOWN
```
Cash Sales:              JMD 450,000.00
Paid Out (Payouts):     -JMD  50,000.00
Paid In:                +JMD       0.00
───────────────────────────────────────
Net Cash Expected:       JMD 400,000.00
Cash Counted (Actual):   JMD 398,500.00
───────────────────────────────────────
VARIANCE:               -JMD   1,500.00 ⚠️ SHORT
```

### 💳 CREDIT/DEBIT CARDS
```
Total Card Sales:        JMD 230,000.00
Number of Transactions:  48
Average Transaction:     JMD   4,791.67
```

### 🎁 GIFT CARDS
```
Total Gift Card Sales:   JMD   5,000.00
Number of Transactions:  2
```

### 📈 SALES SUMMARY
```
Total Sales:             JMD 685,000.00
Number of Transactions:  127
Average Sale:            JMD   5,393.70
Cost of Goods Sold:      JMD       0.00 (if tracked)
───────────────────────────────────────
GROSS PROFIT:            JMD 685,000.00
```

### 💰 PAYMENT METHOD BREAKDOWN
```
Cash:           JMD 450,000.00  (65.7%)
Credit Card:    JMD 230,000.00  (33.6%)
Gift Card:      JMD   5,000.00  ( 0.7%)
───────────────────────────────────────
TOTAL:          JMD 685,000.00
```

### 🏦 CREDIT CARD DETAIL (For Bank Settlement)
```
Time      Order#  Reference        Amount
───────────────────────────────────────────────
09:15 AM  #101    VISA-1234   JMD 3,500.00
09:32 AM  #102    MC-5678     JMD 6,200.00
10:05 AM  #103    AMEX-9012   JMD 8,750.00
... (45 more transactions)
───────────────────────────────────────────────
TOTAL: 48 transactions, JMD 230,000.00
```

---

## Complete Daily Workflow

### Morning (Opening):
1. System automatically tracks from last settlement
2. No action needed - just start selling!

### During the Day:
**When you pay cash out:**
```bash
POST /api/payouts
{
  "amount": 3000,
  "reason": "Petty cash - Office supplies",
  "recipient": "John Doe"
}
```

### Evening (Closing):

**Step 1: Check what system expects**
```bash
GET /api/settlements/calculate
```

**Step 2: Count the cash in till**
- Cashier physically counts all bills and coins
- Example: Counted JMD 398,500.00

**Step 3: Submit settlement**
```bash
POST /api/settlements
{
  "actual_cash": 398500,
  "notes": "Monday closing - Jane Smith"
}
```

**Step 4: Review report**
```bash
GET /api/settlements/1
```

**Step 5: Print/Save for records**
- Settlement report
- Card listing for bank deposit
- Note any variance for investigation

---

## Understanding Variance

### ✅ Variance: JMD 0.00
**Perfect!** Cash matches exactly.

### ✅ Variance: +JMD 500.00 (OVER)
**Good problem!** You have JMD 500 MORE than expected.
- Possible causes: Customer overpaid and you didn't notice, found money

### ⚠️ Variance: -JMD 1,500.00 (SHORT)
**Problem!** You have JMD 1,500 LESS than expected.
- Possible causes: Gave wrong change, forgot to ring up a sale, theft
- Investigate and document

---

## Testing the System

### 1. Make Some Sales
Use your POS to process a few orders with different payment methods:
- 3 cash sales
- 2 card sales
- 1 gift card sale

### 2. Record a Payout
```bash
curl -X POST http://localhost:8000/api/payouts \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 1000,
    "reason": "Test payout",
    "recipient": "Test"
  }'
```

### 3. Calculate Expected
```bash
curl http://localhost:8000/api/settlements/calculate
```

### 4. Close Till
```bash
curl -X POST http://localhost:8000/api/settlements \
  -H "Content-Type: application/json" \
  -d '{
    "actual_cash": 50000,
    "notes": "Test settlement"
  }'
```

### 5. View Report
```bash
curl http://localhost:8000/api/settlements/1
```

---

## Quick Reference Commands

### Record Payout:
```bash
POST /api/payouts
{
  "amount": 5000,
  "reason": "Supplier payment",
  "recipient": "ABC Suppliers"
}
```

### Check Expected Cash:
```bash
GET /api/settlements/calculate
```

### Close Till:
```bash
POST /api/settlements
{
  "actual_cash": 398500,
  "notes": "End of day"
}
```

### View Settlement:
```bash
GET /api/settlements/{id}
```

### List All Settlements:
```bash
GET /api/settlements
```

### List All Payouts:
```bash
GET /api/payouts
```

### Today's Payouts Total:
```bash
GET /api/payouts/today-total
```

---

## Summary

✅ **Payouts** - Track cash paid out from till
✅ **Calculate** - System calculates expected cash
✅ **Count** - Cashier counts actual cash
✅ **Submit** - System calculates variance
✅ **Report** - Comprehensive breakdown with card listing

**Next Settlement:** System automatically starts tracking from this settlement's end time!

All settlements are saved in the database and can be viewed anytime for historical reporting.
