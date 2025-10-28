<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyEmployee;
use App\Models\Order;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    public function __construct(
        private LoyaltyService $loyaltyService
    ) {}

    /**
     * Lookup employee by phone or email
     */
    public function lookup(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
        ]);

        $employee = $this->loyaltyService->lookupEmployee($request->identifier);

        if (!$employee) {
            return response()->json([
                'found' => false,
                'message' => 'No active loyalty employee found with that phone or email'
            ], 404);
        }

        // Get monthly summary
        $summary = $this->loyaltyService->getEmployeeMonthSummary($employee);

        return response()->json([
            'found' => true,
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->full_name,
                'email' => $employee->email,
                'phone' => $employee->phone,
                'company' => [
                    'id' => $employee->company->id,
                    'name' => $employee->company->name,
                    'discount_percentage' => $employee->company->discount_percentage,
                ],
                'monthly_summary' => $summary,
            ],
        ]);
    }

    /**
     * Calculate discount for an order
     */
    public function calculateDiscount(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:loyalty_employees,id',
            'order_subtotal' => 'required|numeric|min:0',
        ]);

        $employee = LoyaltyEmployee::with('company')->findOrFail($request->employee_id);
        $calculation = $this->loyaltyService->calculateDiscount($employee, $request->order_subtotal);

        return response()->json($calculation);
    }

    /**
     * Apply loyalty discount to an order
     */
    public function applyDiscount(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:loyalty_employees,id',
            'order_id' => 'required|exists:orders,id',
        ]);

        $employee = LoyaltyEmployee::with('company')->findOrFail($request->employee_id);
        $order = Order::findOrFail($request->order_id);

        // Check if order already has loyalty discount
        $existingTransaction = $order->loyaltyTransaction;
        if ($existingTransaction) {
            return response()->json([
                'success' => false,
                'message' => 'Order already has a loyalty discount applied'
            ], 422);
        }

        $transaction = $this->loyaltyService->applyDiscount($employee, $order);

        if (!$transaction) {
            $calculation = $this->loyaltyService->calculateDiscount($employee, $order->subtotal);
            return response()->json([
                'success' => false,
                'message' => $calculation['reason'] ?? 'Discount could not be applied',
                'details' => $calculation,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'transaction' => $transaction,
            'discount_amount' => $transaction->discount_amount,
            'message' => 'Loyalty discount applied successfully',
        ]);
    }
}
