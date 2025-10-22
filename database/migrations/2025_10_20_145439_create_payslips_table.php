<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_period_id')->constrained('payroll_periods')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();

            // Hours worked
            $table->decimal('regular_hours', 8, 2)->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);

            // Gross pay breakdown
            $table->decimal('regular_pay_cents', 12, 0)->default(0);
            $table->decimal('overtime_pay_cents', 12, 0)->default(0);
            $table->decimal('bonus_cents', 12, 0)->default(0);
            $table->decimal('gross_pay_cents', 12, 0)->default(0);

            // Deductions
            $table->decimal('nis_cents', 12, 0)->default(0)->comment('National Insurance (3%)');
            $table->decimal('nht_cents', 12, 0)->default(0)->comment('National Housing Trust (2%)');
            $table->decimal('education_tax_cents', 12, 0)->default(0)->comment('Education Tax (2.25%)');
            $table->decimal('paye_cents', 12, 0)->default(0)->comment('Pay As You Earn Income Tax');
            $table->decimal('other_deductions_cents', 12, 0)->default(0);
            $table->decimal('total_deductions_cents', 12, 0)->default(0);

            // Net pay
            $table->decimal('net_pay_cents', 12, 0)->default(0);

            // Status
            $table->enum('status', ['draft', 'approved', 'sent', 'paid'])->default('draft');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['payroll_period_id', 'employee_id']);
            $table->unique(['payroll_period_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
