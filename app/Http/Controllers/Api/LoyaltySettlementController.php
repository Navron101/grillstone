<?php

namespace App\Http\Controllers\Api;

use App\Exports\LoyaltySettlementExport;
use App\Http\Controllers\Controller;
use App\Models\LoyaltyCompany;
use App\Models\LoyaltySettlement;
use App\Models\LoyaltyTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LoyaltySettlementController extends Controller
{
    /**
     * List all settlements
     */
    public function index(Request $request)
    {
        $query = LoyaltySettlement::with('company')
            ->orderBy('period', 'desc');

        // Filter by company if provided
        if ($request->has('company_id')) {
            $query->where('loyalty_company_id', $request->company_id);
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $settlements = $query->get();

        return response()->json($settlements);
    }

    /**
     * Show settlement details with transactions
     */
    public function show(LoyaltySettlement $loyaltySettlement)
    {
        $loyaltySettlement->load([
            'company',
            'transactions' => function($q) {
                $q->with('employee')
                    ->orderBy('created_at', 'desc');
            }
        ]);

        // Group transactions by employee for the report
        $transactionsByEmployee = $loyaltySettlement->transactions
            ->groupBy('loyalty_employee_id')
            ->map(function($transactions) {
                $employee = $transactions->first()->employee;
                return [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->full_name,
                    'employee_number' => $employee->employee_id,
                    'transaction_count' => $transactions->count(),
                    'total_discount' => $transactions->sum('discount_amount'),
                    'transactions' => $transactions->map(fn($t) => [
                        'id' => $t->id,
                        'order_id' => $t->order_id,
                        'date' => $t->created_at->format('Y-m-d H:i'),
                        'order_subtotal' => $t->order_subtotal,
                        'discount_amount' => $t->discount_amount,
                    ])
                ];
            })->values();

        $loyaltySettlement->transactions_by_employee = $transactionsByEmployee;

        return response()->json($loyaltySettlement);
    }

    /**
     * Generate settlement for a company and period
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:loyalty_companies,id',
            'period' => 'required|date_format:Y-m', // e.g., "2025-10"
        ]);

        $company = LoyaltyCompany::findOrFail($validated['company_id']);

        // Check if settlement already exists
        $existing = LoyaltySettlement::where('loyalty_company_id', $company->id)
            ->where('period', $validated['period'])
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Settlement already exists for this period',
                'settlement' => $existing
            ], 422);
        }

        // Generate the settlement
        $settlement = LoyaltySettlement::generateForPeriod($company, $validated['period']);

        $settlement->load('company', 'transactions');

        return response()->json([
            'message' => 'Settlement generated successfully',
            'settlement' => $settlement
        ], 201);
    }

    /**
     * Finalize a settlement (lock it)
     */
    public function finalize(LoyaltySettlement $loyaltySettlement)
    {
        try {
            $loyaltySettlement->finalize();
            return response()->json([
                'message' => 'Settlement finalized successfully',
                'settlement' => $loyaltySettlement
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Mark settlement as sent
     */
    public function markAsSent(LoyaltySettlement $loyaltySettlement)
    {
        $loyaltySettlement->markAsSent();

        return response()->json([
            'message' => 'Settlement marked as sent',
            'settlement' => $loyaltySettlement
        ]);
    }

    /**
     * Record payment for settlement
     */
    public function recordPayment(Request $request, LoyaltySettlement $loyaltySettlement)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $loyaltySettlement->recordPayment($validated['amount']);

        return response()->json([
            'message' => 'Payment recorded successfully',
            'settlement' => $loyaltySettlement
        ]);
    }

    /**
     * Get pending transactions (not yet in a settlement)
     */
    public function pendingTransactions(Request $request)
    {
        $query = LoyaltyTransaction::with(['employee', 'company', 'order'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc');

        if ($request->has('company_id')) {
            $query->where('loyalty_company_id', $request->company_id);
        }

        $transactions = $query->get();

        // Group by company and period
        $grouped = $transactions->groupBy(function($transaction) {
            return $transaction->loyalty_company_id . '-' . $transaction->created_at->format('Y-m');
        })->map(function($transactions) {
            $first = $transactions->first();
            return [
                'company_id' => $first->loyalty_company_id,
                'company_name' => $first->company->name,
                'period' => $first->created_at->format('Y-m'),
                'period_label' => $first->created_at->format('F Y'),
                'transaction_count' => $transactions->count(),
                'total_amount' => $transactions->sum('discount_amount'),
            ];
        })->values();

        return response()->json($grouped);
    }

    /**
     * Delete a draft settlement
     */
    public function destroy(LoyaltySettlement $loyaltySettlement)
    {
        if ($loyaltySettlement->status !== 'draft') {
            return response()->json([
                'message' => 'Can only delete draft settlements'
            ], 422);
        }

        // Set transactions back to pending
        $loyaltySettlement->transactions()->update([
            'status' => 'pending',
            'loyalty_settlement_id' => null,
            'settled_at' => null,
        ]);

        $loyaltySettlement->delete();

        return response()->json([
            'message' => 'Settlement deleted successfully'
        ]);
    }

    /**
     * Export settlement as PDF
     */
    public function exportPdf(LoyaltySettlement $loyaltySettlement)
    {
        $loyaltySettlement->load([
            'company',
            'transactions' => function($q) {
                $q->with('employee')
                    ->orderBy('created_at', 'desc');
            }
        ]);

        // Group transactions by employee
        $transactionsByEmployee = $loyaltySettlement->transactions
            ->groupBy('loyalty_employee_id')
            ->map(function($transactions) {
                $employee = $transactions->first()->employee;
                return [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->full_name,
                    'employee_number' => $employee->employee_id,
                    'transaction_count' => $transactions->count(),
                    'total_discount' => $transactions->sum('discount_amount'),
                    'transactions' => $transactions->map(fn($t) => [
                        'id' => $t->id,
                        'order_id' => $t->order_id,
                        'date' => $t->created_at->format('Y-m-d H:i'),
                        'order_subtotal' => $t->order_subtotal,
                        'discount_amount' => $t->discount_amount,
                    ])
                ];
            })->values();

        $pdf = Pdf::loadView('loyalty.settlement-pdf', [
            'settlement' => $loyaltySettlement,
            'transactionsByEmployee' => $transactionsByEmployee,
        ]);

        $filename = 'settlement-' . $loyaltySettlement->company->name . '-' . $loyaltySettlement->period . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export settlement as Excel
     */
    public function exportExcel(LoyaltySettlement $loyaltySettlement)
    {
        $loyaltySettlement->load([
            'company',
            'transactions' => function($q) {
                $q->with('employee')
                    ->orderBy('created_at', 'desc');
            }
        ]);

        // Group transactions by employee
        $transactionsByEmployee = $loyaltySettlement->transactions
            ->groupBy('loyalty_employee_id')
            ->map(function($transactions) {
                $employee = $transactions->first()->employee;
                return [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->full_name,
                    'employee_number' => $employee->employee_id,
                    'transaction_count' => $transactions->count(),
                    'total_discount' => $transactions->sum('discount_amount'),
                    'transactions' => $transactions->map(fn($t) => [
                        'id' => $t->id,
                        'order_id' => $t->order_id,
                        'date' => $t->created_at->format('Y-m-d H:i'),
                        'order_subtotal' => $t->order_subtotal,
                        'discount_amount' => $t->discount_amount,
                    ])
                ];
            })->values();

        $filename = 'settlement-' . $loyaltySettlement->company->name . '-' . $loyaltySettlement->period . '.xlsx';

        return Excel::download(
            new LoyaltySettlementExport($loyaltySettlement, $transactionsByEmployee),
            $filename
        );
    }
}
