<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyEmployee;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;

class LoyaltyEmployeeController extends Controller
{
    public function __construct(
        private LoyaltyService $loyaltyService
    ) {}

    public function index(Request $request)
    {
        $query = LoyaltyEmployee::with('company')
            ->orderBy('last_name')
            ->orderBy('first_name');

        // Filter by company if provided
        if ($request->has('company_id')) {
            $query->where('loyalty_company_id', $request->company_id);
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $employees = $query->get();

        return response()->json($employees);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'loyalty_company_id' => 'required|exists:loyalty_companies,id',
            'employee_id' => 'nullable|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:loyalty_employees,email',
            'phone' => 'required|string|unique:loyalty_employees,phone',
            'status' => 'nullable|in:active,inactive,suspended',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
        ]);

        $employee = LoyaltyEmployee::create($validated);
        $employee->load('company');

        return response()->json($employee, 201);
    }

    public function show(LoyaltyEmployee $loyaltyEmployee)
    {
        $loyaltyEmployee->load([
            'company',
            'transactions' => fn($q) => $q->latest()->limit(20),
        ]);

        // Add monthly summary
        $summary = $this->loyaltyService->getEmployeeMonthSummary($loyaltyEmployee);
        $loyaltyEmployee->monthly_summary = $summary;

        return response()->json($loyaltyEmployee);
    }

    public function update(Request $request, LoyaltyEmployee $loyaltyEmployee)
    {
        $validated = $request->validate([
            'loyalty_company_id' => 'sometimes|required|exists:loyalty_companies,id',
            'employee_id' => 'nullable|string|max:255',
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:loyalty_employees,email,' . $loyaltyEmployee->id,
            'phone' => 'sometimes|required|string|unique:loyalty_employees,phone,' . $loyaltyEmployee->id,
            'status' => 'nullable|in:active,inactive,suspended',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
        ]);

        $loyaltyEmployee->update($validated);
        $loyaltyEmployee->load('company');

        return response()->json($loyaltyEmployee);
    }

    public function destroy(LoyaltyEmployee $loyaltyEmployee)
    {
        // Check if there are pending transactions
        $hasPendingTransactions = $loyaltyEmployee->transactions()
            ->where('status', 'pending')
            ->exists();

        if ($hasPendingTransactions) {
            return response()->json([
                'message' => 'Cannot delete employee with pending transactions'
            ], 422);
        }

        $loyaltyEmployee->delete();

        return response()->json(['message' => 'Employee deleted successfully']);
    }
}
