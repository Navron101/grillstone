<?php

namespace App\Services;

use App\Models\LoyaltyEmployee;
use App\Models\LoyaltyTransaction;
use App\Models\Order;

class LoyaltyService
{
    /**
     * Calculate discount for an employee's order
     * Returns array with discount details or error message
     */
    public function calculateDiscount(LoyaltyEmployee $employee, float $orderSubtotal): array
    {
        // Check eligibility
        if (!$employee->isEligible()) {
            return [
                'eligible' => false,
                'reason' => 'Employee is not in good standing or company is inactive',
                'discount_amount' => 0,
            ];
        }

        $company = $employee->company;
        $discountPercentage = $company->discount_percentage;

        // Calculate base discount
        $baseDiscount = ($orderSubtotal * $discountPercentage) / 100;

        // Apply per-order cap
        if ($company->per_order_cap && $baseDiscount > $company->per_order_cap) {
            $baseDiscount = $company->per_order_cap;
        }

        // Check per-employee monthly cap
        if ($company->per_employee_monthly_cap) {
            $employeeMonthlyTotal = $employee->getCurrentMonthTotal();
            $availableEmployeeCap = $company->per_employee_monthly_cap - $employeeMonthlyTotal;

            if ($availableEmployeeCap <= 0) {
                return [
                    'eligible' => false,
                    'reason' => 'Employee has reached monthly discount cap',
                    'discount_amount' => 0,
                    'monthly_used' => $employeeMonthlyTotal,
                    'monthly_cap' => $company->per_employee_monthly_cap,
                ];
            }

            if ($baseDiscount > $availableEmployeeCap) {
                $baseDiscount = $availableEmployeeCap;
            }
        }

        // Check company monthly cap
        if ($company->company_monthly_cap) {
            $companyMonthlyTotal = $company->getCurrentMonthTotal();
            $availableCompanyCap = $company->company_monthly_cap - $companyMonthlyTotal;

            if ($availableCompanyCap <= 0) {
                return [
                    'eligible' => false,
                    'reason' => 'Company has reached monthly discount cap',
                    'discount_amount' => 0,
                    'company_monthly_used' => $companyMonthlyTotal,
                    'company_monthly_cap' => $company->company_monthly_cap,
                ];
            }

            if ($baseDiscount > $availableCompanyCap) {
                $baseDiscount = $availableCompanyCap;
            }
        }

        return [
            'eligible' => true,
            'discount_percentage' => $discountPercentage,
            'discount_amount' => round($baseDiscount, 2),
            'final_total' => round($orderSubtotal - $baseDiscount, 2),
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->full_name,
                'company' => $company->name,
            ],
        ];
    }

    /**
     * Apply loyalty discount to an order
     */
    public function applyDiscount(LoyaltyEmployee $employee, Order $order): ?LoyaltyTransaction
    {
        $calculation = $this->calculateDiscount($employee, $order->subtotal);

        if (!$calculation['eligible'] || $calculation['discount_amount'] <= 0) {
            return null;
        }

        // Create loyalty transaction
        $transaction = LoyaltyTransaction::create([
            'loyalty_company_id' => $employee->loyalty_company_id,
            'loyalty_employee_id' => $employee->id,
            'order_id' => $order->id,
            'order_subtotal' => $order->subtotal,
            'discount_percentage' => $calculation['discount_percentage'],
            'discount_amount' => $calculation['discount_amount'],
            'status' => 'pending',
        ]);

        return $transaction;
    }

    /**
     * Lookup employee by phone or email
     */
    public function lookupEmployee(string $identifier): ?LoyaltyEmployee
    {
        $identifier = trim($identifier);

        // Try email first
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return LoyaltyEmployee::where('email', $identifier)
                ->where('status', 'active')
                ->with('company')
                ->first();
        }

        // Try phone (remove any formatting)
        $phone = preg_replace('/[^0-9]/', '', $identifier);

        return LoyaltyEmployee::where('phone', 'LIKE', '%' . $phone . '%')
            ->where('status', 'active')
            ->with('company')
            ->first();
    }

    /**
     * Reverse a loyalty transaction (for refunds)
     */
    public function reverseTransaction(Order $order, string $reason = null): void
    {
        $transaction = LoyaltyTransaction::where('order_id', $order->id)
            ->whereIn('status', ['pending', 'settled'])
            ->first();

        if ($transaction) {
            $transaction->reverse($reason ?? 'Order refunded');
        }
    }

    /**
     * Get employee's current month summary
     */
    public function getEmployeeMonthSummary(LoyaltyEmployee $employee): array
    {
        $company = $employee->company;
        $employeeTotal = $employee->getCurrentMonthTotal();
        $companyTotal = $company->getCurrentMonthTotal();

        return [
            'employee_monthly_total' => $employeeTotal,
            'employee_monthly_cap' => $company->per_employee_monthly_cap,
            'employee_remaining' => $company->per_employee_monthly_cap
                ? max(0, $company->per_employee_monthly_cap - $employeeTotal)
                : null,
            'company_monthly_total' => $companyTotal,
            'company_monthly_cap' => $company->company_monthly_cap,
            'company_remaining' => $company->company_monthly_cap
                ? max(0, $company->company_monthly_cap - $companyTotal)
                : null,
        ];
    }
}
