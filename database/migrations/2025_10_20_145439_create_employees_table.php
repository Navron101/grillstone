<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_number', 20)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 150)->unique();
            $table->string('phone', 20)->nullable();
            $table->string('trn', 20)->nullable()->comment('Tax Registration Number (Jamaica)');
            $table->string('nis', 20)->nullable()->comment('National Insurance Scheme Number');

            // Address
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('parish', 100)->nullable();

            // Employment details
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('position', 100);
            $table->date('hire_date');
            $table->date('termination_date')->nullable();
            $table->enum('employment_type', ['full-time', 'part-time', 'contract', 'casual'])->default('full-time');
            $table->enum('employment_status', ['active', 'on-leave', 'terminated', 'suspended'])->default('active');

            // Compensation
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->decimal('salary_per_period', 10, 2)->nullable()->comment('Fortnightly salary if salaried');
            $table->boolean('is_salaried')->default(false)->comment('True = salaried, False = hourly');

            // Bank details for payment
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_account', 50)->nullable();
            $table->string('bank_branch', 100)->nullable();

            // Emergency contact
            $table->string('emergency_contact_name', 150)->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->string('emergency_contact_relationship', 50)->nullable();

            // Notes
            $table->text('notes')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
