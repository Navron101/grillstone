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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_category_id')->constrained()->onDelete('restrict');
            $table->date('expense_date');
            $table->string('reference_number')->nullable();
            $table->unsignedBigInteger('amount_cents');
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->string('payment_method')->nullable(); // cash, card, bank_transfer, etc.
            $table->foreignId('recorded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
