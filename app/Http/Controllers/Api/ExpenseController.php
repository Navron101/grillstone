<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['category', 'recordedBy']);

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('expense_category_id', $request->category_id);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('expense_date', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('expense_date', '<=', $request->to_date);
        }

        // Search by description or reference
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        $expenses = $query->orderBy('expense_date', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->get();

        return response()->json(['expenses' => $expenses]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'expense_date' => 'required|date',
            'reference_number' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'payment_method' => 'nullable|string|max:255',
        ]);

        // Convert amount to cents
        $data['amount_cents'] = (int)($data['amount'] * 100);
        unset($data['amount']);

        // Add recorded_by if authenticated
        if (auth()->check()) {
            $data['recorded_by'] = auth()->id();
        }

        $expense = Expense::create($data);
        $expense->load(['category', 'recordedBy']);

        return response()->json([
            'message' => 'Expense recorded successfully',
            'expense' => $expense
        ], 201);
    }

    public function show($id)
    {
        $expense = Expense::with(['category', 'recordedBy'])->findOrFail($id);
        return response()->json(['expense' => $expense]);
    }

    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);

        $data = $request->validate([
            'expense_category_id' => 'sometimes|exists:expense_categories,id',
            'expense_date' => 'sometimes|date',
            'reference_number' => 'nullable|string|max:255',
            'amount' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'payment_method' => 'nullable|string|max:255',
        ]);

        // Convert amount to cents if provided
        if (isset($data['amount'])) {
            $data['amount_cents'] = (int)($data['amount'] * 100);
            unset($data['amount']);
        }

        $expense->update($data);
        $expense->load(['category', 'recordedBy']);

        return response()->json([
            'message' => 'Expense updated successfully',
            'expense' => $expense
        ]);
    }

    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();

        return response()->json([
            'message' => 'Expense deleted successfully'
        ]);
    }

    /**
     * Get expense report by category for a date range
     */
    public function report(Request $request)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        // Get expenses grouped by category
        $expensesByCategory = Expense::with('category')
            ->whereBetween('expense_date', [$fromDate, $toDate])
            ->get()
            ->groupBy('expense_category_id');

        $report = [];
        $totalAmount = 0;

        foreach ($expensesByCategory as $categoryId => $expenses) {
            $categoryTotal = $expenses->sum('amount_cents');
            $totalAmount += $categoryTotal;

            $report[] = [
                'category_id' => $categoryId,
                'category_name' => $expenses->first()->category->name,
                'expense_count' => $expenses->count(),
                'total_cents' => $categoryTotal,
                'total' => $categoryTotal / 100,
                'expenses' => $expenses->map(function($expense) {
                    return [
                        'id' => $expense->id,
                        'expense_date' => $expense->expense_date->format('Y-m-d'),
                        'description' => $expense->description,
                        'reference_number' => $expense->reference_number,
                        'amount_cents' => $expense->amount_cents,
                        'amount' => $expense->amount_cents / 100,
                        'payment_method' => $expense->payment_method,
                    ];
                })
            ];
        }

        // Sort by total amount descending
        usort($report, function($a, $b) {
            return $b['total_cents'] - $a['total_cents'];
        });

        // Get monthly breakdown
        $monthlyExpenses = Expense::whereBetween('expense_date', [$fromDate, $toDate])
            ->get()
            ->groupBy(function($expense) {
                return $expense->expense_date->format('Y-m');
            })
            ->map(function($expenses, $month) {
                return [
                    'month' => $month,
                    'month_label' => date('M Y', strtotime($month . '-01')),
                    'total_cents' => $expenses->sum('amount_cents'),
                    'total' => $expenses->sum('amount_cents') / 100,
                    'count' => $expenses->count(),
                ];
            })
            ->sortBy('month')
            ->values();

        return response()->json([
            'report' => $report,
            'monthly_breakdown' => $monthlyExpenses,
            'summary' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'total_categories' => count($report),
                'total_expenses' => Expense::whereBetween('expense_date', [$fromDate, $toDate])->count(),
                'total_amount_cents' => $totalAmount,
                'total_amount' => $totalAmount / 100,
            ]
        ]);
    }
}
