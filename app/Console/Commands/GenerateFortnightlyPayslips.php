<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\PayrollService;
use App\Services\PayslipPdfService;
use Carbon\Carbon;

class GenerateFortnightlyPayslips extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payroll:generate-fortnightly
                            {--auto-email : Automatically email payslips to employees}
                            {--approve : Automatically approve the period}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically generate fortnightly payslips and optionally email them';

    protected $payrollService;
    protected $payslipPdfService;

    public function __construct(PayrollService $payrollService, PayslipPdfService $payslipPdfService)
    {
        parent::__construct();
        $this->payrollService = $payrollService;
        $this->payslipPdfService = $payslipPdfService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting fortnightly payroll generation...');

        // Calculate period dates (last 14 days)
        $endDate = Carbon::now()->subDay(); // Yesterday
        $startDate = $endDate->copy()->subDays(13); // 14 days total
        $payDate = Carbon::now()->addDays(2); // Pay in 2 days

        $this->info("Period: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");
        $this->info("Pay Date: {$payDate->format('Y-m-d')}");

        // Check if period already exists
        $existing = DB::table('payroll_periods')
            ->where('start_date', $startDate->format('Y-m-d'))
            ->where('end_date', $endDate->format('Y-m-d'))
            ->first();

        if ($existing) {
            $this->error('❌ Payroll period already exists for this date range!');
            return 1;
        }

        // Create payroll period
        $periodId = DB::table('payroll_periods')->insertGetId([
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'pay_date' => $payDate->format('Y-m-d'),
            'status' => 'draft',
            'notes' => 'Auto-generated fortnightly payroll',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->info("✅ Created payroll period #{$periodId}");

        // Generate payslips
        $this->info('📊 Generating payslips...');
        $bar = $this->output->createProgressBar();

        try {
            $result = $this->payrollService->generatePayslipsForPeriod($periodId);

            $this->newLine();
            $this->info("✅ Generated {$result['generated']} payslips");

            if (!empty($result['errors'])) {
                $this->warn("⚠️  " . count($result['errors']) . " errors occurred:");
                foreach ($result['errors'] as $error) {
                    $this->error("  - {$error['employee_name']}: {$error['error']}");
                }
            }

            // Update period status
            DB::table('payroll_periods')
                ->where('id', $periodId)
                ->update([
                    'status' => 'processing',
                    'updated_at' => now(),
                ]);

            // Auto-approve if requested
            if ($this->option('approve')) {
                $this->info('📝 Auto-approving payroll period...');

                DB::table('payroll_periods')
                    ->where('id', $periodId)
                    ->update([
                        'status' => 'approved',
                        'processed_at' => now(),
                        'updated_at' => now(),
                    ]);

                DB::table('payslips')
                    ->where('payroll_period_id', $periodId)
                    ->update([
                        'status' => 'approved',
                        'updated_at' => now(),
                    ]);

                $this->info('✅ Period approved');
            }

            // Auto-email if requested
            if ($this->option('auto-email') && $this->option('approve')) {
                $this->info('📧 Emailing payslips to employees...');

                $payslips = DB::table('payslips')
                    ->where('payroll_period_id', $periodId)
                    ->where('status', 'approved')
                    ->get();

                $sent = 0;
                $emailBar = $this->output->createProgressBar(count($payslips));

                foreach ($payslips as $payslip) {
                    try {
                        $this->payslipPdfService->emailPayslipPdf($payslip->id);
                        $sent++;
                        $emailBar->advance();
                    } catch (\Exception $e) {
                        $this->error("\n  Failed to email payslip #{$payslip->id}: {$e->getMessage()}");
                    }
                }

                $emailBar->finish();
                $this->newLine();
                $this->info("✅ Sent {$sent} payslip emails");
            }

            $this->newLine();
            $this->info('🎉 Payroll generation complete!');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Period ID', $periodId],
                    ['Payslips Generated', $result['generated']],
                    ['Errors', count($result['errors'])],
                    ['Status', $this->option('approve') ? 'Approved' : 'Processing'],
                ]
            );

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Failed to generate payslips: {$e->getMessage()}");
            return 1;
        }
    }
}
