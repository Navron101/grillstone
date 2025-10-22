<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Services\PayrollService;
use App\Services\PayslipPdfService;

class PayrollController extends Controller
{
    protected $payrollService;
    protected $payslipPdfService;

    public function __construct(PayrollService $payrollService, PayslipPdfService $payslipPdfService)
    {
        $this->payrollService = $payrollService;
        $this->payslipPdfService = $payslipPdfService;
    }

    /**
     * List all payroll periods
     */
    public function periods(Request $request)
    {
        $query = DB::table('payroll_periods as pp')
            ->leftJoin('users as u', 'u.id', '=', 'pp.processed_by')
            ->select([
                'pp.*',
                DB::raw("CONCAT(u.name) as processed_by_name"),
            ])
            ->orderByDesc('pp.start_date');

        if ($request->has('status')) {
            $query->where('pp.status', $request->status);
        }

        $periods = $query->get();

        // Get payslip counts for each period
        foreach ($periods as $period) {
            $period->payslip_count = DB::table('payslips')
                ->where('payroll_period_id', $period->id)
                ->count();
        }

        return response()->json($periods);
    }

    /**
     * Get a single payroll period
     */
    public function showPeriod($id)
    {
        $period = DB::table('payroll_periods as pp')
            ->leftJoin('users as u', 'u.id', '=', 'pp.processed_by')
            ->where('pp.id', $id)
            ->select([
                'pp.*',
                DB::raw("CONCAT(u.name) as processed_by_name"),
            ])
            ->first();

        if (!$period) {
            return response()->json(['error' => 'Period not found'], 404);
        }

        // Get payslip count
        $period->payslip_count = DB::table('payslips')
            ->where('payroll_period_id', $period->id)
            ->count();

        return response()->json($period);
    }

    /**
     * Create a new payroll period
     */
    public function createPeriod(Request $request)
    {
        $data = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'pay_date' => 'required|date|after_or_equal:end_date',
            'notes' => 'nullable|string',
        ]);

        $id = DB::table('payroll_periods')->insertGetId([
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'pay_date' => $data['pay_date'],
            'status' => 'draft',
            'notes' => $data['notes'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['id' => $id], 201);
    }

    /**
     * Generate payslips for a payroll period
     */
    public function generatePayslips(Request $request, $periodId)
    {
        try {
            $includeStatutoryDeductions = $request->input('include_statutory_deductions', true);

            $result = $this->payrollService->generatePayslipsForPeriod($periodId, $includeStatutoryDeductions);

            // Update period status
            DB::table('payroll_periods')
                ->where('id', $periodId)
                ->update([
                    'status' => 'processing',
                    'updated_at' => now(),
                ]);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * List payslips for a period
     */
    public function payslips($periodId)
    {
        $payslips = DB::table('payslips as ps')
            ->join('employees as e', 'e.id', '=', 'ps.employee_id')
            ->leftJoin('departments as d', 'd.id', '=', 'e.department_id')
            ->where('ps.payroll_period_id', $periodId)
            ->select([
                'ps.*',
                DB::raw("CONCAT(e.first_name, ' ', e.last_name) as employee_name"),
                'e.employee_number',
                'e.email as employee_email',
                'd.name as department_name',
            ])
            ->orderBy('e.employee_number')
            ->get();

        return response()->json($payslips);
    }

    /**
     * Get a single payslip
     */
    public function showPayslip($periodId, $payslipId)
    {
        $payslip = DB::table('payslips as ps')
            ->join('employees as e', 'e.id', '=', 'ps.employee_id')
            ->join('payroll_periods as pp', 'pp.id', '=', 'ps.payroll_period_id')
            ->leftJoin('departments as d', 'd.id', '=', 'e.department_id')
            ->where('ps.id', $payslipId)
            ->where('ps.payroll_period_id', $periodId)
            ->select([
                'ps.*',
                DB::raw("CONCAT(e.first_name, ' ', e.last_name) as employee_name"),
                'e.employee_number',
                'e.email as employee_email',
                'e.trn',
                'e.nis',
                'e.address',
                'e.position',
                'd.name as department_name',
                'pp.start_date',
                'pp.end_date',
                'pp.pay_date',
            ])
            ->first();

        if (!$payslip) {
            return response()->json(['error' => 'Payslip not found'], 404);
        }

        return response()->json($payslip);
    }

    /**
     * Update a payslip (for adjustments)
     */
    public function updatePayslip($periodId, $payslipId, Request $request)
    {
        $data = $request->validate([
            'bonus_cents' => 'nullable|integer|min:0',
            'other_deductions_cents' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $payslip = DB::table('payslips')->where('id', $payslipId)->first();
        if (!$payslip) {
            return response()->json(['error' => 'Payslip not found'], 404);
        }

        // Recalculate totals
        $grossPayCents = $payslip->regular_pay_cents + $payslip->overtime_pay_cents;
        if (isset($data['bonus_cents'])) {
            $grossPayCents += $data['bonus_cents'];
        } else {
            $grossPayCents += $payslip->bonus_cents;
        }

        $totalDeductionsCents = $payslip->nis_cents + $payslip->nht_cents +
                                $payslip->education_tax_cents + $payslip->paye_cents;

        if (isset($data['other_deductions_cents'])) {
            $totalDeductionsCents += $data['other_deductions_cents'];
        } else {
            $totalDeductionsCents += $payslip->other_deductions_cents;
        }

        $netPayCents = $grossPayCents - $totalDeductionsCents;

        $updateData = array_filter([
            'bonus_cents' => $data['bonus_cents'] ?? null,
            'other_deductions_cents' => $data['other_deductions_cents'] ?? null,
            'notes' => $data['notes'] ?? null,
            'gross_pay_cents' => $grossPayCents,
            'total_deductions_cents' => $totalDeductionsCents,
            'net_pay_cents' => $netPayCents,
            'updated_at' => now(),
        ], fn($val) => $val !== null);

        DB::table('payslips')->where('id', $payslipId)->update($updateData);

        return response()->json(['ok' => true]);
    }

    /**
     * Approve all payslips in a period
     */
    public function approvePeriod($periodId)
    {
        DB::table('payroll_periods')
            ->where('id', $periodId)
            ->update([
                'status' => 'approved',
                'processed_by' => Auth::id(),
                'processed_at' => now(),
                'updated_at' => now(),
            ]);

        DB::table('payslips')
            ->where('payroll_period_id', $periodId)
            ->update([
                'status' => 'approved',
                'updated_at' => now(),
            ]);

        return response()->json(['ok' => true]);
    }

    /**
     * Email payslips to all employees
     */
    public function emailPayslips($periodId)
    {
        $period = DB::table('payroll_periods')->where('id', $periodId)->first();
        if (!$period || $period->status !== 'approved') {
            return response()->json([
                'error' => 'Period must be approved before sending payslips'
            ], 400);
        }

        $payslips = DB::table('payslips as ps')
            ->join('employees as e', 'e.id', '=', 'ps.employee_id')
            ->where('ps.payroll_period_id', $periodId)
            ->where('ps.status', 'approved')
            ->select([
                'ps.*',
                'e.email',
                DB::raw("CONCAT(e.first_name, ' ', e.last_name) as employee_name"),
            ])
            ->get();

        $sent = 0;
        $errors = [];

        foreach ($payslips as $payslip) {
            try {
                $this->sendPayslipEmail($payslip, $period);

                DB::table('payslips')
                    ->where('id', $payslip->id)
                    ->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                        'updated_at' => now(),
                    ]);

                $sent++;
            } catch (\Exception $e) {
                $errors[] = [
                    'employee' => $payslip->employee_name,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'sent' => $sent,
            'errors' => $errors,
        ]);
    }

    /**
     * Send payslip email to an employee
     */
    private function sendPayslipEmail($payslip, $period)
    {
        $data = [
            'employee_name' => $payslip->employee_name,
            'period_start' => $period->start_date,
            'period_end' => $period->end_date,
            'pay_date' => $period->pay_date,
            'regular_hours' => $payslip->regular_hours,
            'overtime_hours' => $payslip->overtime_hours,
            'gross_pay' => $payslip->gross_pay_cents / 100,
            'net_pay' => $payslip->net_pay_cents / 100,
            'nis' => $payslip->nis_cents / 100,
            'nht' => $payslip->nht_cents / 100,
            'education_tax' => $payslip->education_tax_cents / 100,
            'paye' => $payslip->paye_cents / 100,
        ];

        Mail::raw($this->formatPayslipEmail($data), function ($message) use ($payslip) {
            $message->to($payslip->email)
                    ->subject('Your Payslip - ' . date('F Y'));
        });
    }

    /**
     * Format payslip email
     */
    private function formatPayslipEmail($data)
    {
        return "Dear {$data['employee_name']},\n\n" .
               "Please find your payslip for the period {$data['period_start']} to {$data['period_end']}.\n\n" .
               "Pay Date: {$data['pay_date']}\n\n" .
               "Hours Worked:\n" .
               "Regular Hours: {$data['regular_hours']}\n" .
               "Overtime Hours: {$data['overtime_hours']}\n\n" .
               "Gross Pay: JMD " . number_format($data['gross_pay'], 2) . "\n\n" .
               "Deductions:\n" .
               "NIS (3%): JMD " . number_format($data['nis'], 2) . "\n" .
               "NHT (2%): JMD " . number_format($data['nht'], 2) . "\n" .
               "Education Tax (2.25%): JMD " . number_format($data['education_tax'], 2) . "\n" .
               "PAYE: JMD " . number_format($data['paye'], 2) . "\n\n" .
               "Net Pay: JMD " . number_format($data['net_pay'], 2) . "\n\n" .
               "Thank you,\n" .
               "Grillstone Management";
    }

    /**
     * Mark period as paid
     */
    public function markPaid($periodId)
    {
        DB::table('payroll_periods')
            ->where('id', $periodId)
            ->update([
                'status' => 'paid',
                'updated_at' => now(),
            ]);

        DB::table('payslips')
            ->where('payroll_period_id', $periodId)
            ->update([
                'status' => 'paid',
                'paid_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json(['ok' => true]);
    }

    /**
     * Download payslip as PDF
     */
    public function downloadPayslipPdf($periodId, $payslipId)
    {
        try {
            $pdf = $this->payslipPdfService->generatePayslipPdf($payslipId);

            $payslip = DB::table('payslips as ps')
                ->join('employees as e', 'e.id', '=', 'ps.employee_id')
                ->join('payroll_periods as pp', 'pp.id', '=', 'ps.payroll_period_id')
                ->where('ps.id', $payslipId)
                ->where('ps.payroll_period_id', $periodId)
                ->select([
                    'e.employee_number',
                    'pp.end_date',
                ])
                ->first();

            if (!$payslip) {
                return response()->json(['error' => 'Payslip not found'], 404);
            }

            $fileName = sprintf(
                'Payslip_%s_%s.pdf',
                $payslip->employee_number,
                date('Y-m-d', strtotime($payslip->end_date))
            );

            return $pdf->download($fileName);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Email single payslip PDF to employee
     */
    public function emailSinglePayslipPdf($periodId, $payslipId)
    {
        try {
            $result = $this->payslipPdfService->emailPayslipPdf($payslipId);

            return response()->json([
                'success' => true,
                'message' => 'Payslip sent successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to send payslip: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Email all payslips as PDFs for a period
     */
    public function emailPayslipsPdf($periodId)
    {
        $period = DB::table('payroll_periods')->where('id', $periodId)->first();
        if (!$period || $period->status !== 'approved') {
            return response()->json([
                'error' => 'Period must be approved before sending payslips'
            ], 400);
        }

        $payslips = DB::table('payslips as ps')
            ->join('employees as e', 'e.id', '=', 'ps.employee_id')
            ->where('ps.payroll_period_id', $periodId)
            ->where('ps.status', 'approved')
            ->select([
                'ps.id',
                DB::raw("CONCAT(e.first_name, ' ', e.last_name) as employee_name"),
            ])
            ->get();

        $sent = 0;
        $errors = [];

        foreach ($payslips as $payslip) {
            try {
                $this->payslipPdfService->emailPayslipPdf($payslip->id);
                $sent++;
            } catch (\Exception $e) {
                $errors[] = [
                    'employee' => $payslip->employee_name,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'sent' => $sent,
            'errors' => $errors,
        ]);
    }
}
