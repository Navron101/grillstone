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
        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loyalty_company_id')->constrained()->onDelete('cascade');
            $table->foreignId('loyalty_employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');

            $table->decimal('order_subtotal', 10, 2); // Original order amount before discount
            $table->decimal('discount_percentage', 5, 2); // Percentage applied
            $table->decimal('discount_amount', 10, 2); // Actual discount given

            $table->enum('status', ['pending', 'settled', 'reversed'])->default('pending');
            $table->unsignedBigInteger('loyalty_settlement_id')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->timestamp('reversed_at')->nullable();
            $table->text('reversal_reason')->nullable();

            $table->timestamps();

            // Indexes for reporting and settlement generation
            $table->index(['loyalty_company_id', 'status', 'created_at']);
            $table->index(['loyalty_employee_id', 'status', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_transactions');
    }
};
