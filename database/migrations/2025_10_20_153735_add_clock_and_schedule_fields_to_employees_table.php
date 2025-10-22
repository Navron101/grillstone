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
            // Default working hours per day
            $table->decimal('standard_hours_per_day', 5, 2)->default(8.00)->after('is_salaried');

            // Work schedule (JSON) - e.g., {"monday": true, "tuesday": true, ...}
            $table->json('work_schedule')->nullable()->after('standard_hours_per_day');

            // Overtime settings
            $table->decimal('overtime_rate_multiplier', 3, 2)->default(1.5)->after('work_schedule');

            // Clock system enabled
            $table->boolean('clock_system_enabled')->default(true)->after('overtime_rate_multiplier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'standard_hours_per_day',
                'work_schedule',
                'overtime_rate_multiplier',
                'clock_system_enabled'
            ]);
        });
    }
};
