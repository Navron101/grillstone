<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialReportController extends Controller
{
    /**
     * Generate Balance Sheet
     */
    public function balanceSheet(Request $request)
    {
        $asOfDate = $request->input('as_of_date', now()->format('Y-m-d'));
        $asOf = Carbon::parse($asOfDate)->endOfDay();

        // ASSETS
        $assets = $this->calculateAssets($asOf);

        // LIABILITIES
        $liabilities = $this->calculateLiabilities($asOf);

        // EQUITY
        $equity = $this->calculateEquity($asOf);

        return response()->json([
            'report_type' => 'Balance Sheet',
            'as_of_date' => $asOfDate,
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'total_assets' => $assets['total'],
            'total_liabilities' => $liabilities['total'],
            'total_equity' => $equity['total'],
            'total_liabilities_and_equity' => $liabilities['total'] + $equity['total'],
            'balanced' => abs($assets['total'] - ($liabilities['total'] + $equity['total'])) < 100, // Within $1
        ]);
    }

    /**
     * Generate Income Statement (Profit & Loss)
     */
    public function incomeStatement(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // REVENUE
        $revenue = $this->calculateRevenue($start, $end);

        // COST OF GOODS SOLD
        $cogs = $this->calculateCOGS($start, $end);

        // GROSS PROFIT
        $grossProfit = $revenue['total'] - $cogs['total'];

        // OPERATING EXPENSES
        $expenses = $this->calculateExpenses($start, $end);

        // NET INCOME
        $netIncome = $grossProfit - $expenses['total'];

        return response()->json([
            'report_type' => 'Income Statement',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'revenue' => $revenue,
            'cogs' => $cogs,
            'gross_profit_cents' => $grossProfit,
            'gross_profit_margin' => $revenue['total'] > 0 ? ($grossProfit / $revenue['total']) * 100 : 0,
            'expenses' => $expenses,
            'net_income_cents' => $netIncome,
            'net_profit_margin' => $revenue['total'] > 0 ? ($netIncome / $revenue['total']) * 100 : 0,
        ]);
    }

    /**
     * Profit & Loss Statement (same as Income Statement but different presentation)
     */
    public function profitAndLoss(Request $request)
    {
        return $this->incomeStatement($request);
    }

    /**
     * Generate Cash Flow Statement
     */
    public function cashFlowStatement(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // Opening Cash Balance
        $openingBalance = $this->getCashBalance($start);

        // OPERATING ACTIVITIES
        $operatingActivities = $this->calculateOperatingCashFlow($start, $end);

        // INVESTING ACTIVITIES
        $investingActivities = $this->calculateInvestingCashFlow($start, $end);

        // FINANCING ACTIVITIES
        $financingActivities = $this->calculateFinancingCashFlow($start, $end);

        // Net Cash Flow
        $netCashFlow = $operatingActivities['total'] + $investingActivities['total'] + $financingActivities['total'];

        // Closing Cash Balance
        $closingBalance = $openingBalance + $netCashFlow;

        return response()->json([
            'report_type' => 'Cash Flow Statement',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'opening_balance_cents' => $openingBalance,
            'operating_activities' => $operatingActivities,
            'investing_activities' => $investingActivities,
            'financing_activities' => $financingActivities,
            'net_cash_flow_cents' => $netCashFlow,
            'closing_balance_cents' => $closingBalance,
        ]);
    }

    // ========== PRIVATE HELPER METHODS ==========

    private function calculateAssets($asOf)
    {
        // Current Assets
        $cash = $this->getCashBalance($asOf);

        // Inventory value (using FIFO lots)
        $inventory = DB::table('inventory_lots')
            ->where('created_at', '<=', $asOf)
            ->sum(DB::raw('qty_on_hand * unit_cost_cents'));

        // Accounts Receivable (if tracked)
        $accountsReceivable = 0;

        $currentAssets = $cash + $inventory + $accountsReceivable;

        // Fixed Assets (equipment, furniture, etc.) - would come from accounts table
        $fixedAssets = 0;

        return [
            'current_assets' => [
                'cash' => $cash,
                'inventory' => $inventory,
                'accounts_receivable' => $accountsReceivable,
                'total' => $currentAssets,
            ],
            'fixed_assets' => [
                'equipment' => $fixedAssets,
                'total' => $fixedAssets,
            ],
            'total' => $currentAssets + $fixedAssets,
        ];
    }

    private function calculateLiabilities($asOf)
    {
        // Accounts Payable
        $accountsPayable = 0;

        // Payroll Liabilities
        $payrollLiabilities = DB::table('payslips')
            ->where('created_at', '<=', $asOf)
            ->where('status', 'pending')
            ->sum('net_pay_cents');

        // Employee Tabs (outstanding balances)
        $employeeTabs = DB::table('employee_tab_items')
            ->where('created_at', '<=', $asOf)
            ->where('status', 'unpaid')
            ->sum('amount_cents');

        $currentLiabilities = $accountsPayable + $payrollLiabilities + $employeeTabs;

        // Long-term liabilities (loans, etc.)
        $longTermLiabilities = 0;

        return [
            'current_liabilities' => [
                'accounts_payable' => $accountsPayable,
                'payroll_payable' => $payrollLiabilities,
                'employee_tabs' => $employeeTabs,
                'total' => $currentLiabilities,
            ],
            'long_term_liabilities' => [
                'loans' => $longTermLiabilities,
                'total' => $longTermLiabilities,
            ],
            'total' => $currentLiabilities + $longTermLiabilities,
        ];
    }

    private function calculateEquity($asOf)
    {
        // Owner's Equity (would be tracked in accounts)
        $ownersEquity = 0;

        // Retained Earnings = Total Revenue - Total Expenses (all time)
        $totalRevenue = DB::table('payments')
            ->where('created_at', '<=', $asOf)
            ->sum('amount_cents');

        $totalCOGS = DB::table('till_settlements')
            ->where('settlement_date', '<=', $asOf)
            ->sum('cogs_cents');

        // Expenses (payroll + payouts + other)
        $totalPayroll = DB::table('payslips')
            ->where('created_at', '<=', $asOf)
            ->where('status', 'paid')
            ->sum('net_pay_cents');

        $totalPayouts = DB::table('payouts')
            ->where('payout_date', '<=', $asOf)
            ->where('status', 'completed')
            ->sum('amount_cents');

        $retainedEarnings = $totalRevenue - $totalCOGS - $totalPayroll - $totalPayouts;

        return [
            'owners_equity' => $ownersEquity,
            'retained_earnings' => $retainedEarnings,
            'total' => $ownersEquity + $retainedEarnings,
        ];
    }

    private function calculateRevenue($start, $end)
    {
        $salesRevenue = DB::table('payments')
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount_cents');

        return [
            'sales_revenue' => $salesRevenue,
            'other_revenue' => 0,
            'total' => $salesRevenue,
        ];
    }

    private function calculateCOGS($start, $end)
    {
        $cogs = DB::table('till_settlements')
            ->whereBetween('settlement_date', [$start, $end])
            ->sum('cogs_cents');

        return [
            'cost_of_goods_sold' => $cogs,
            'total' => $cogs,
        ];
    }

    private function calculateExpenses($start, $end)
    {
        // Payroll expenses
        $payroll = DB::table('payslips')
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'paid')
            ->sum('net_pay_cents');

        // Other operating expenses (would come from journal entries)
        $otherExpenses = 0;

        return [
            'payroll' => $payroll,
            'operating_expenses' => $otherExpenses,
            'total' => $payroll + $otherExpenses,
        ];
    }

    private function getCashBalance($asOf)
    {
        // Cash from sales
        $cashIn = DB::table('payments')
            ->where('created_at', '<=', $asOf)
            ->whereIn('method', ['cash', 'wallet'])
            ->sum('amount_cents');

        // Cash paid out
        $cashOut = DB::table('payouts')
            ->where('payout_date', '<=', $asOf)
            ->where('status', 'completed')
            ->sum('amount_cents');

        // Payroll paid in cash
        $payrollCash = DB::table('payslips')
            ->where('created_at', '<=', $asOf)
            ->where('status', 'paid')
            ->sum('net_pay_cents');

        return $cashIn - $cashOut - $payrollCash;
    }

    private function calculateOperatingCashFlow($start, $end)
    {
        // Cash received from customers
        $cashReceipts = DB::table('payments')
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('method', ['cash', 'wallet'])
            ->sum('amount_cents');

        // Cash paid to suppliers (would track via accounts payable)
        $cashPaidSuppliers = 0;

        // Cash paid for expenses (payroll)
        $cashPaidExpenses = DB::table('payslips')
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'paid')
            ->sum('net_pay_cents');

        $netOperatingCash = $cashReceipts - $cashPaidSuppliers - $cashPaidExpenses;

        return [
            'cash_receipts' => $cashReceipts,
            'cash_paid_suppliers' => -$cashPaidSuppliers,
            'cash_paid_expenses' => -$cashPaidExpenses,
            'total' => $netOperatingCash,
        ];
    }

    private function calculateInvestingCashFlow($start, $end)
    {
        // Purchase/sale of fixed assets
        $equipmentPurchases = 0;

        return [
            'equipment_purchases' => -$equipmentPurchases,
            'total' => -$equipmentPurchases,
        ];
    }

    private function calculateFinancingCashFlow($start, $end)
    {
        // Owner investments/withdrawals
        $ownerInvestments = 0;
        $ownerWithdrawals = DB::table('payouts')
            ->whereBetween('payout_date', [$start, $end])
            ->where('status', 'completed')
            ->sum('amount_cents');

        return [
            'owner_investments' => $ownerInvestments,
            'owner_withdrawals' => -$ownerWithdrawals,
            'total' => $ownerInvestments - $ownerWithdrawals,
        ];
    }
}
