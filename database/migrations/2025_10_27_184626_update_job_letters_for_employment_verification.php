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
        Schema::table('job_letters', function (Blueprint $table) {
            // Drop old promotion-related columns
            $table->dropColumn(['old_position', 'new_position', 'effective_date']);

            // Add employment verification columns
            $table->string('recipient_name')->nullable()->after('employee_name');
            $table->string('recipient_organization')->nullable()->after('recipient_name');
            $table->text('recipient_address')->nullable()->after('recipient_organization');
            $table->string('letter_purpose')->default('employment_verification')->after('recipient_address');
            $table->date('letter_date')->default(now())->after('letter_purpose');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_letters', function (Blueprint $table) {
            // Restore old columns
            $table->string('old_position')->nullable();
            $table->string('new_position');
            $table->date('effective_date');

            // Drop new columns
            $table->dropColumn(['recipient_name', 'recipient_organization', 'recipient_address', 'letter_purpose', 'letter_date']);
        });
    }
};
