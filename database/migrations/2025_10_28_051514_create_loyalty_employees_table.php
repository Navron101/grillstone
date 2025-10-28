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
        Schema::create('loyalty_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loyalty_company_id')->constrained()->onDelete('cascade');

            $table->string('employee_id')->nullable(); // Company's internal employee ID
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->unique();

            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index for quick lookups at POS
            $table->index(['email', 'status']);
            $table->index(['phone', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_employees');
    }
};
