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
        Schema::create('loyalty_companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('address')->nullable();

            // Discount configuration
            $table->decimal('discount_percentage', 5, 2); // e.g., 5.00 to 20.00
            $table->decimal('per_order_cap', 10, 2)->nullable(); // Max discount per order
            $table->decimal('per_employee_monthly_cap', 10, 2)->nullable(); // Max discount per employee per month
            $table->decimal('company_monthly_cap', 10, 2)->nullable(); // Max discount for entire company per month

            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->date('contract_start_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_companies');
    }
};
