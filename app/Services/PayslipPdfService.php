<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PayslipPdfService
{
    /**
     * Generate a beautiful PDF payslip
     *
     * @param int $payslipId
     * @return \Barryvdh\DomPDF\PDF
     * @throws \Exception
     */
    public function generatePayslipPdf(int $payslipId)
    {
        $payslipData = $this->getPayslipData($payslipId);

        $pdf = Pdf::loadView('pdf.payslip', ['payslip' => $payslipData]);

        // Set paper size and orientation
        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }

    /**
     * Generate and email PDF payslip to employee
     *
     * @param int $payslipId
     * @return bool
     * @throws \Exception
     */
    public function emailPayslipPdf(int $payslipId): bool
    {
        $payslipData = $this->getPayslipData($payslipId);

        if (empty($payslipData['employee']['email'])) {
            throw new \Exception("Employee does not have an email address");
        }

        $pdf = $this->generatePayslipPdf($payslipId);

        $fileName = sprintf(
            'Payslip_%s_%s.pdf',
            $payslipData['employee']['employee_number'],
            date('Y-m-d', strtotime($payslipData['period']['end_date']))
        );

        try {
            Mail::send('emails.payslip', ['payslip' => $payslipData], function ($message) use ($payslipData, $pdf, $fileName) {
                $message->to($payslipData['employee']['email'], $payslipData['employee']['full_name'])
                    ->subject('Payslip for ' . $payslipData['period']['period_label'])
                    ->attachData($pdf->output(), $fileName, [
                        'mime' => 'application/pdf',
                    ]);
            });

            // Update sent_at timestamp
            DB::table('payslips')
                ->where('id', $payslipId)
                ->update([
                    'sent_at' => now(),
                    'status' => 'sent'
                ]);

            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to send email: " . $e->getMessage());
        }
    }

    /**
     * Get formatted payslip data with all related information
     *
     * @param int $payslipId
     * @return array
     * @throws \Exception
     */
    private function getPayslipData(int $payslipId): array
    {
        $payslip = DB::table('payslips')
            ->where('id', $payslipId)
            ->first();

        if (!$payslip) {
            throw new \Exception("Payslip not found");
        }

        $employee = DB::table('employees')
            ->where('id', $payslip->employee_id)
            ->first();

        if (!$employee) {
            throw new \Exception("Employee not found");
        }

        $period = DB::table('payroll_periods')
            ->where('id', $payslip->payroll_period_id)
            ->first();

        if (!$period) {
            throw new \Exception("Payroll period not found");
        }

        $department = null;
        if ($employee->department_id) {
            $department = DB::table('departments')
                ->where('id', $employee->department_id)
                ->first();
        }

        // Get tab items for this payslip
        $tabItems = DB::table('employee_tab_items')
            ->where('payslip_id', $payslipId)
            ->where('status', 'deducted')
            ->get();

        return [
            'id' => $payslip->id,
            'employee' => [
                'full_name' => $employee->first_name . ' ' . $employee->last_name,
                'employee_number' => $employee->employee_number,
                'position' => $employee->position,
                'department' => $department ? $department->name : 'N/A',
                'email' => $employee->email,
                'trn' => $employee->trn ?? 'N/A',
                'nis' => $employee->nis ?? 'N/A',
            ],
            'period' => [
                'start_date' => $period->start_date,
                'end_date' => $period->end_date,
                'pay_date' => $period->pay_date,
                'period_label' => date('M d', strtotime($period->start_date)) . ' - ' . date('M d, Y', strtotime($period->end_date)),
            ],
            'hours' => [
                'regular' => number_format($payslip->regular_hours, 2),
                'overtime' => number_format($payslip->overtime_hours, 2),
            ],
            'earnings' => [
                'regular_pay' => $this->formatMoney($payslip->regular_pay_cents),
                'regular_pay_raw' => $payslip->regular_pay_cents / 100,
                'overtime_pay' => $this->formatMoney($payslip->overtime_pay_cents),
                'overtime_pay_raw' => $payslip->overtime_pay_cents / 100,
                'bonus' => $this->formatMoney($payslip->bonus_cents),
                'bonus_raw' => $payslip->bonus_cents / 100,
                'gross_pay' => $this->formatMoney($payslip->gross_pay_cents),
                'gross_pay_raw' => $payslip->gross_pay_cents / 100,
            ],
            'deductions' => [
                'nis' => $this->formatMoney($payslip->nis_cents),
                'nis_raw' => $payslip->nis_cents / 100,
                'nis_rate' => '3%',
                'nht' => $this->formatMoney($payslip->nht_cents),
                'nht_raw' => $payslip->nht_cents / 100,
                'nht_rate' => '2%',
                'education_tax' => $this->formatMoney($payslip->education_tax_cents),
                'education_tax_raw' => $payslip->education_tax_cents / 100,
                'education_tax_rate' => '2.25%',
                'paye' => $this->formatMoney($payslip->paye_cents),
                'paye_raw' => $payslip->paye_cents / 100,
                'other' => $this->formatMoney($payslip->other_deductions_cents),
                'other_raw' => $payslip->other_deductions_cents / 100,
                'tab_deductions' => $this->formatMoney($payslip->tab_deductions_cents ?? 0),
                'tab_deductions_raw' => ($payslip->tab_deductions_cents ?? 0) / 100,
                'total' => $this->formatMoney($payslip->total_deductions_cents),
                'total_raw' => $payslip->total_deductions_cents / 100,
            ],
            'tab_items' => $tabItems->map(function ($item) {
                return [
                    'description' => $item->description,
                    'amount' => $this->formatMoney($item->amount_cents),
                    'amount_raw' => $item->amount_cents / 100,
                    'date' => $item->tab_date,
                ];
            })->toArray(),
            'include_statutory_deductions' => $payslip->include_statutory_deductions ?? true,
            'net_pay' => $this->formatMoney($payslip->net_pay_cents),
            'net_pay_raw' => $payslip->net_pay_cents / 100,
            'status' => ucfirst($payslip->status),
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Format amount as JMD currency with commas
     *
     * @param int $cents
     * @return string
     */
    private function formatMoney(int $cents): string
    {
        $amount = $cents / 100;
        return 'JMD ' . number_format($amount, 2);
    }
}
