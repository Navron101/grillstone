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
        Schema::create('till_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Who performed the settlement
            $table->timestamp('settlement_date')->nullable(); // When the settlement was performed
            $table->timestamp('period_start')->nullable(); // Start of the settlement period
            $table->timestamp('period_end')->nullable(); // End of the settlement period

            // System-calculated amounts (what the system expects)
            $table->integer('expected_cash_cents')->default(0); // Expected cash in till
            $table->integer('expected_card_cents')->default(0); // Expected card payments
            $table->integer('expected_gift_card_cents')->default(0); // Expected gift card payments

            // Cashier-entered amounts (what was actually counted)
            $table->integer('actual_cash_cents'); // Cash counted by cashier

            // Variance (difference between expected and actual)
            $table->integer('cash_variance_cents')->default(0); // actual - expected

            // Sales breakdown
            $table->integer('total_sales_cents')->default(0); // Gross sales
            $table->integer('num_transactions')->default(0); // Number of sales
            $table->integer('cogs_cents')->default(0); // Cost of goods sold
            $table->integer('profit_cents')->default(0); // Profit (sales - cogs)

            // Paid in/Paid out
            $table->integer('paid_in_cents')->default(0); // Money added to till
            $table->integer('paid_out_cents')->default(0); // Money removed from till (payouts)

            // Net amounts after paid in/out
            $table->integer('net_cash_cents')->default(0); // Cash after paid in/out
            $table->integer('net_card_cents')->default(0); // Cards (no paid in/out)

            // Status and notes
            $table->enum('status', ['draft', 'completed', 'reviewed'])->default('completed');
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('till_settlements');
    }
};
