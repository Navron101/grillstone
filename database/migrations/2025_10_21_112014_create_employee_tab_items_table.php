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
        Schema::create('employee_tab_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('description'); // What was purchased (e.g., "Lunch - Jerk Chicken")
            $table->integer('amount_cents'); // Amount in cents
            $table->date('tab_date'); // When the item was added to the tab
            $table->enum('status', ['pending', 'deducted', 'cancelled'])->default('pending');
            $table->foreignId('payslip_id')->nullable()->constrained()->onDelete('set null'); // Links to payslip when deducted
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_tab_items');
    }
};
