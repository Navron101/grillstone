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
        Schema::create('loyalty_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loyalty_company_id')->constrained()->onDelete('cascade');

            $table->string('period'); // e.g., '2025-10' for October 2025
            $table->date('period_start');
            $table->date('period_end');

            $table->integer('transaction_count')->default(0);
            $table->decimal('total_amount', 10, 2)->default(0); // Total discount amount to be recovered

            $table->enum('status', ['draft', 'finalized', 'sent', 'paid', 'partially_paid'])->default('draft');
            $table->timestamp('finalized_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->text('notes')->nullable();

            $table->timestamps();

            // Unique constraint: one settlement per company per period
            $table->unique(['loyalty_company_id', 'period']);
            $table->index(['status', 'period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_settlements');
    }
};
