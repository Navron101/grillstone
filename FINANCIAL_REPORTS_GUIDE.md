# Financial Reports System - User Guide

## Overview

The Grillstone POS system now includes comprehensive financial reporting capabilities with four main report types:

1. **Balance Sheet** - Snapshot of assets, liabilities, and equity
2. **Income Statement** - Revenue and expenses over a period
3. **Profit & Loss Statement** - Detailed profitability analysis
4. **Cash Flow Statement** - Cash movement tracking

## Accessing Financial Reports

Navigate to: **`/reports/financial`** or click on "Financial Reports" in the Reports menu.

## Report Types

### 1. Balance Sheet

**Purpose:** Shows the financial position of your business at a specific point in time.

**Formula:** Assets = Liabilities + Equity

**Components:**
- **Assets**
  - Current Assets: Cash, Inventory, Accounts Receivable
  - Fixed Assets: Equipment, Furniture

- **Liabilities**
  - Current Liabilities: Accounts Payable, Payroll Payable, Employee Tabs
  - Long-term Liabilities: Loans

- **Equity**
  - Owner's Equity
  - Retained Earnings (cumulative profits)

**How to Use:**
- Select "Balance Sheet" tab
- Choose "As of Date" (defaults to today)
- View the snapshot of your financial position
- A balanced sheet means: Total Assets = Total Liabilities + Equity

### 2. Income Statement

**Purpose:** Shows profitability over a specific period.

**Formula:** Net Income = Revenue - COGS - Operating Expenses

**Components:**
- **Revenue**
  - Sales Revenue (from POS transactions)
  - Other Revenue

- **Cost of Goods Sold (COGS)**
  - Direct costs of ingredients used in sold items

- **Operating Expenses**
  - Payroll expenses
  - Other operating costs

**Key Metrics:**
- **Gross Profit** = Revenue - COGS
- **Gross Profit Margin** = (Gross Profit / Revenue) × 100
- **Net Income** = Gross Profit - Operating Expenses
- **Net Profit Margin** = (Net Income / Revenue) × 100

**How to Use:**
- Select "Income Statement" tab
- Choose Start Date and End Date
- Use quick filters: Today, This Week, This Month, This Quarter, This Year
- Review profitability metrics

### 3. Profit & Loss Statement

**Purpose:** Similar to Income Statement but with enhanced visual presentation and breakdown.

**Features:**
- Color-coded sections (Income, COGS, Expenses)
- Summary cards showing key totals
- Detailed expense breakdown
- Performance indicators
- Expense ratios (as % of revenue)

**How to Use:**
- Select "Profit & Loss" tab
- Choose date range
- View detailed breakdown of income and expenses
- Analyze expense ratios and profitability metrics

### 4. Cash Flow Statement

**Purpose:** Tracks actual cash movement in and out of the business.

**Formula:** Closing Cash = Opening Cash + Net Cash Flow

**Components:**

1. **Operating Activities**
   - Cash from customers
   - Cash paid to suppliers
   - Cash paid for expenses

2. **Investing Activities**
   - Equipment purchases
   - Asset sales

3. **Financing Activities**
   - Owner investments
   - Owner withdrawals (payouts)

**How to Use:**
- Select "Cash Flow" tab
- Choose date range
- Review cash movement across all activities
- Verify closing balance matches actual cash

## Quick Date Filters

All date-based reports include quick filters:
- **Today** - Current day only
- **This Week** - Sunday to today
- **This Month** - 1st of month to today
- **This Quarter** - Start of quarter to today
- **This Year** - January 1st to today

## Data Sources

The financial reports pull data from:

1. **Sales Revenue**: `payments` table (all payment methods)
2. **COGS**: `till_settlements` table (tracked COGS from FIFO inventory)
3. **Inventory**: `inventory_lots` table (FIFO lot values)
4. **Payroll**: `payslips` table (employee wages)
5. **Payouts**: `payouts` table (cash withdrawals)
6. **Employee Tabs**: `employee_tab_items` table (outstanding employee charges)
7. **Cash Balance**: Calculated from payments, payouts, and payroll

## Exporting Reports

Each report includes export options:

1. **Export to PDF** (coming soon)
   - Click "Export to PDF" button
   - Download professional PDF version

2. **Print**
   - Click "Print" button
   - Opens browser print dialog
   - Print-optimized layout

## Understanding Report Calculations

### Cash Calculation
```
Cash Balance = Cash from Sales - Cash Payouts - Cash Payroll
```

### Inventory Valuation
```
Inventory Value = Sum of (Quantity on Hand × Unit Cost) for all lots
```

### Retained Earnings
```
Retained Earnings = Total Revenue - Total COGS - Total Expenses (all time)
```

### Net Income
```
Net Income = Revenue - COGS - Operating Expenses (for period)
```

## Tips for Accurate Reporting

1. **Regular Till Settlements**: Close your till daily to ensure accurate COGS tracking
2. **Track All Expenses**: Record all payouts with proper descriptions
3. **Employee Tabs**: Mark items as paid when settled
4. **Inventory Accuracy**: Perform regular stocktakes to verify inventory values
5. **Payroll Processing**: Process payroll regularly and mark as paid

## Common Use Cases

### Monthly Financial Review
1. Set date range to current month
2. View Profit & Loss report
3. Check profitability margins
4. Compare revenue vs. expenses

### Quarter-End Reporting
1. Set date range to quarter (Jan-Mar, Apr-Jun, Jul-Sep, Oct-Dec)
2. Generate all four reports
3. Export to PDF for records
4. Review cash flow trends

### Year-End Analysis
1. Set date range to full year
2. Review Income Statement for annual performance
3. Check Balance Sheet for year-end position
4. Analyze Cash Flow for liquidity trends

### Daily Quick Check
1. Use "Today" quick filter
2. View Profit & Loss for daily performance
3. Check Cash Flow for today's cash movement

## Database Schema

### Accounts Table
Stores chart of accounts for double-entry bookkeeping:
- Account types: Asset, Liability, Equity, Revenue, Expense, COGS
- Hierarchical structure with parent-child relationships
- System accounts cannot be deleted

### Journal Entries
Records all financial transactions:
- Double-entry bookkeeping (Debit = Credit)
- Links to source transactions (orders, payments, settlements)
- Audit trail with timestamps and user tracking

### Journal Entry Lines
Individual debit/credit entries:
- Links to specific accounts
- Stores amounts in cents
- Includes memo for descriptions

## Future Enhancements

The following features are planned:
- [ ] PDF export with professional formatting
- [ ] Email reports to stakeholders
- [ ] Scheduled automatic report generation
- [ ] Budget vs. Actual comparisons
- [ ] Trend analysis and charts
- [ ] Multi-period comparisons
- [ ] Custom date range presets
- [ ] Account reconciliation tools
- [ ] Tax reporting templates

## Troubleshooting

### Balance Sheet Not Balanced
- Verify all transactions are properly recorded
- Check that COGS calculations are complete
- Ensure payouts and payroll are properly tracked
- Review employee tab balances

### Missing Revenue
- Confirm till settlements are completed
- Check that payments are properly recorded
- Verify date range selection

### Incorrect COGS
- Complete daily till settlements
- Verify inventory lot costs
- Check recipe components are properly configured

### Cash Balance Mismatch
- Compare with actual cash on hand
- Review till settlement records
- Check payout records
- Verify payroll payment records

## Support

For issues or questions about financial reports:
1. Check this documentation
2. Review the system audit trail
3. Verify source data in respective modules
4. Contact system administrator

---

**Note:** This is a living document and will be updated as new features are added.
