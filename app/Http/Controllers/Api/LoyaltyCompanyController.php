<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyCompany;
use Illuminate\Http\Request;

class LoyaltyCompanyController extends Controller
{
    public function index()
    {
        $companies = LoyaltyCompany::withCount(['employees', 'transactions'])
            ->orderBy('name')
            ->get();

        return response()->json($companies);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'per_order_cap' => 'nullable|numeric|min:0',
            'per_employee_monthly_cap' => 'nullable|numeric|min:0',
            'company_monthly_cap' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,inactive,suspended',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date|after_or_equal:contract_start_date',
            'notes' => 'nullable|string',
        ]);

        $company = LoyaltyCompany::create($validated);

        return response()->json($company, 201);
    }

    public function show(LoyaltyCompany $loyaltyCompany)
    {
        $loyaltyCompany->load([
            'employees' => fn($q) => $q->orderBy('last_name'),
            'transactions' => fn($q) => $q->latest()->limit(10),
        ]);

        $loyaltyCompany->loadCount('employees', 'transactions');

        // Add monthly summary
        $loyaltyCompany->monthly_total = $loyaltyCompany->getCurrentMonthTotal();

        return response()->json($loyaltyCompany);
    }

    public function update(Request $request, LoyaltyCompany $loyaltyCompany)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'discount_percentage' => 'sometimes|required|numeric|min:0|max:100',
            'per_order_cap' => 'nullable|numeric|min:0',
            'per_employee_monthly_cap' => 'nullable|numeric|min:0',
            'company_monthly_cap' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,inactive,suspended',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date|after_or_equal:contract_start_date',
            'notes' => 'nullable|string',
        ]);

        $loyaltyCompany->update($validated);

        return response()->json($loyaltyCompany);
    }

    public function destroy(LoyaltyCompany $loyaltyCompany)
    {
        // Check if there are pending settlements
        $hasPendingSettlements = $loyaltyCompany->settlements()
            ->whereIn('status', ['draft', 'finalized', 'sent'])
            ->exists();

        if ($hasPendingSettlements) {
            return response()->json([
                'message' => 'Cannot delete company with pending settlements'
            ], 422);
        }

        $loyaltyCompany->delete();

        return response()->json(['message' => 'Company deleted successfully']);
    }
}
