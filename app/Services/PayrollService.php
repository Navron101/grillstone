<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PayrollService
{
    // Jamaica statutory rates (2025)
    const NIS_RATE = 0.03;           // 3% National Insurance
    const NHT_RATE = 0.02;           // 2% National Housing Trust
    const EDUCATION_TAX_RATE = 0.0225; // 2.25% Education Tax

    // PAYE tax bands for Jamaica (annual, converted to fortnightly)
    const PAYE_THRESHOLD_ANNUAL = 1500096; // First JMD 1,500,096 is tax-free
    const PAYE_THRESHOLD_FORTNIGHTLY = 57696; // Per fortnight

    /**
     * Calculate payslip for an employee for a given period
     *
     * @param bool $includeStatutoryDeductions Whether to calculate NIS, NHT, Education Tax, PAYE
     */
    public function calculatePayslip(int $employeeId, int $payrollPeriodId, bool $includeStatutoryDeductions = true): array
    {
        $employee = DB::table('employees')->where('id', $employeeId)->first();
        if (!$employee) {
            throw new \Exception("Employee not found");
        }

        $period = DB::table('payroll_periods')->where('id', $payrollPeriodId)->first();
        if (!$period) {
            throw new \Exception("Payroll period not found");
        }

        // Get approved time logs for this period
        $timeLogs = DB::table('time_logs')
            ->where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereBetween('work_date', [$period->start_date, $period->end_date])
            ->get();

        $regularHours = $timeLogs->sum('regular_hours');
        $overtimeHours = $timeLogs->sum('overtime_hours');

        // Get overtime multiplier (default 1.5)
        $overtimeMultiplier = $employee->overtime_rate_multiplier ?? 1.5;

        // Get pay frequency (default to 'hourly')
        $payFrequency = $employee->pay_frequency ?? 'hourly';

        // Calculate gross pay based on pay frequency
        if ($payFrequency === 'hourly') {
            // Hourly employees: pay = hourly_rate × hours
            $hourlyRate = $employee->hourly_rate ?? 0;
            if ($hourlyRate <= 0) {
                throw new \Exception("Employee {$employee->first_name} {$employee->last_name} has no hourly rate set");
            }
            $regularPayCents = round($hourlyRate * $regularHours * 100);
            $overtimePayCents = round($hourlyRate * $overtimeMultiplier * $overtimeHours * 100);
        } elseif ($payFrequency === 'weekly') {
            // Weekly employees: salary for the week × 2 weeks in fortnight, plus overtime
            $weeklyRate = $employee->salary_amount ?? 0;
            if ($weeklyRate <= 0) {
                throw new \Exception("Employee {$employee->first_name} {$employee->last_name} has no weekly salary set");
            }
            $fortnightlySalary = $weeklyRate * 2;
            $regularPayCents = round($fortnightlySalary * 100);

            // Calculate hourly rate for overtime: weekly salary ÷ standard hours per week
            $standardHoursPerWeek = ($employee->standard_hours_per_day ?? 8) * 5; // 5 days
            $effectiveHourlyRate = $weeklyRate / $standardHoursPerWeek;
            $overtimePayCents = $overtimeHours > 0
                ? round($effectiveHourlyRate * $overtimeMultiplier * $overtimeHours * 100)
                : 0;
        } elseif ($payFrequency === 'fortnightly') {
            // Fortnightly salaried employees: fixed salary for the fortnight
            $salaryAmount = $employee->salary_amount ?? 0;
            if ($salaryAmount <= 0) {
                throw new \Exception("Employee {$employee->first_name} {$employee->last_name} has no fortnightly salary set");
            }
            $regularPayCents = round($salaryAmount * 100);

            // Calculate hourly rate for overtime: fortnightly salary ÷ standard hours in fortnight
            $standardHoursPerFortnight = ($employee->standard_hours_per_day ?? 8) * 10; // 10 working days
            $effectiveHourlyRate = $salaryAmount / $standardHoursPerFortnight;
            $overtimePayCents = $overtimeHours > 0
                ? round($effectiveHourlyRate * $overtimeMultiplier * $overtimeHours * 100)
                : 0;
        } else {
            // Fallback to hourly
            $regularPayCents = round(($employee->hourly_rate ?? 0) * $regularHours * 100);
            $overtimePayCents = round(($employee->hourly_rate ?? 0) * $overtimeMultiplier * $overtimeHours * 100);
        }

        $bonusCents = 0; // Can be added manually
        $grossPayCents = $regularPayCents + $overtimePayCents + $bonusCents;
        $grossPay = $grossPayCents / 100;

        // Calculate statutory deductions (only if flag is true)
        if ($includeStatutoryDeductions) {
            $nisCents = round($grossPay * self::NIS_RATE * 100);
            $nhtCents = round($grossPay * self::NHT_RATE * 100);
            $educationTaxCents = round($grossPay * self::EDUCATION_TAX_RATE * 100);
            $payeCents = $this->calculatePAYE($grossPay);
        } else {
            $nisCents = 0;
            $nhtCents = 0;
            $educationTaxCents = 0;
            $payeCents = 0;
        }

        // Get pending tab items for this employee
        $tabItems = DB::table('employee_tab_items')
            ->where('employee_id', $employeeId)
            ->where('status', 'pending')
            ->get();

        $tabDeductionsCents = $tabItems->sum('amount_cents');

        $totalDeductionsCents = $nisCents + $nhtCents + $educationTaxCents + $payeCents + $tabDeductionsCents;
        $netPayCents = $grossPayCents - $totalDeductionsCents;

        return [
            'employee_id' => $employeeId,
            'payroll_period_id' => $payrollPeriodId,
            'regular_hours' => $regularHours,
            'overtime_hours' => $overtimeHours,
            'regular_pay_cents' => $regularPayCents,
            'overtime_pay_cents' => $overtimePayCents,
            'bonus_cents' => $bonusCents,
            'gross_pay_cents' => $grossPayCents,
            'nis_cents' => $nisCents,
            'nht_cents' => $nhtCents,
            'education_tax_cents' => $educationTaxCents,
            'paye_cents' => $payeCents,
            'other_deductions_cents' => 0,
            'tab_deductions_cents' => $tabDeductionsCents,
            'total_deductions_cents' => $totalDeductionsCents,
            'net_pay_cents' => $netPayCents,
            'status' => 'draft',
            'include_statutory_deductions' => $includeStatutoryDeductions,
            'tab_items' => $tabItems->toArray(),
        ];
    }

    /**
     * Calculate PAYE (Pay As You Earn) income tax for Jamaica
     * Simplified progressive tax calculation
     */
    private function calculatePAYE(float $grossPayFortnightly): int
    {
        // If below threshold, no PAYE
        if ($grossPayFortnightly <= self::PAYE_THRESHOLD_FORTNIGHTLY) {
            return 0;
        }

        $taxableAmount = $grossPayFortnightly - self::PAYE_THRESHOLD_FORTNIGHTLY;

        // Jamaica PAYE rates (simplified):
        // First JMD 6,000,000 annual (230,769 fortnightly): 25%
        // Above that: 30%

        $fortnightlyBandLimit = 230769;

        if ($taxableAmount <= $fortnightlyBandLimit) {
            return round($taxableAmount * 0.25 * 100);
        } else {
            $firstBandTax = $fortnightlyBandLimit * 0.25;
            $secondBandTax = ($taxableAmount - $fortnightlyBandLimit) * 0.30;
            return round(($firstBandTax + $secondBandTax) * 100);
        }
    }

    /**
     * Generate payslips for all active employees in a payroll period
     *
     * @param bool $includeStatutoryDeductions Whether to include NIS, NHT, Education Tax, PAYE
     */
    public function generatePayslipsForPeriod(int $payrollPeriodId, bool $includeStatutoryDeductions = true): array
    {
        $employees = DB::table('employees')
            ->where('employment_status', 'active')
            ->where('is_active', true)
            ->get();

        $generated = [];
        $errors = [];

        foreach ($employees as $employee) {
            try {
                // Check if payslip already exists
                $existing = DB::table('payslips')
                    ->where('payroll_period_id', $payrollPeriodId)
                    ->where('employee_id', $employee->id)
                    ->first();

                if ($existing) {
                    continue; // Skip if already generated
                }

                $payslipData = $this->calculatePayslip($employee->id, $payrollPeriodId, $includeStatutoryDeductions);

                // Extract tab items before inserting (not a DB column)
                $tabItems = $payslipData['tab_items'] ?? [];
                unset($payslipData['tab_items']);

                $payslipData['created_at'] = now();
                $payslipData['updated_at'] = now();

                $id = DB::table('payslips')->insertGetId($payslipData);

                // Mark tab items as deducted and link to payslip
                if (!empty($tabItems)) {
                    $tabItemIds = array_column($tabItems, 'id');
                    DB::table('employee_tab_items')
                        ->whereIn('id', $tabItemIds)
                        ->update([
                            'status' => 'deducted',
                            'payslip_id' => $id,
                            'updated_at' => now()
                        ]);
                }

                $generated[] = $id;
            } catch (\Exception $e) {
                $errors[] = [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'generated' => count($generated),
            'errors' => $errors,
            'payslip_ids' => $generated
        ];
    }
}
