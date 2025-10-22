<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Add pay_frequency field to support hourly, weekly, and fortnightly pay
            $table->enum('pay_frequency', ['hourly', 'weekly', 'fortnightly'])
                ->default('hourly')
                ->after('is_salaried')
                ->comment('How often the employee is paid');

            // Rename salary_per_period to salary_amount for clarity
            $table->renameColumn('salary_per_period', 'salary_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('pay_frequency');
            $table->renameColumn('salary_amount', 'salary_per_period');
        });
    }
};
