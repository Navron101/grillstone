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
        Schema::create('employment_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->string('contract_type')->default('employment'); // employment, probation, permanent
            $table->string('employee_name');
            $table->text('employee_address');
            $table->string('position');
            $table->unsignedBigInteger('salary_amount_cents')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable(); // null for permanent contracts
            $table->longText('contract_html'); // Generated contract HTML
            $table->enum('status', ['draft', 'sent', 'signed', 'cancelled'])->default('draft');
            $table->timestamp('sent_at')->nullable();
            $table->string('sent_to_email')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employment_contracts');
    }
};
