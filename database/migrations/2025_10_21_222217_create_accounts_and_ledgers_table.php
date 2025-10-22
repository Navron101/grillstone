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
        // Chart of Accounts
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // e.g., 1000, 1100, 2000
            $table->string('name'); // e.g., Cash, Accounts Receivable
            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense', 'cogs']); // Account type
            $table->enum('sub_type', [
                // Assets
                'current_asset', 'fixed_asset', 'other_asset',
                // Liabilities
                'current_liability', 'long_term_liability',
                // Equity
                'owner_equity', 'retained_earnings',
                // Revenue
                'sales_revenue', 'other_revenue',
                // Expenses
                'operating_expense', 'administrative_expense', 'other_expense',
                // COGS
                'cost_of_goods_sold'
            ])->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('accounts')->onDelete('cascade');
            $table->integer('level')->default(0); // Hierarchy level
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false); // System accounts cannot be deleted
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Journal Entries (double-entry bookkeeping)
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number')->unique(); // e.g., JE-2025-0001
            $table->date('entry_date');
            $table->enum('type', ['general', 'sales', 'purchase', 'payment', 'receipt', 'adjustment', 'closing']);
            $table->string('reference_type')->nullable(); // e.g., Order, Payment, Settlement
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of the reference
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'posted', 'void'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->index(['reference_type', 'reference_id']);
        });

        // Journal Entry Lines
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_id')->constrained()->onDelete('restrict');
            $table->integer('debit_cents')->default(0);
            $table->integer('credit_cents')->default(0);
            $table->text('memo')->nullable();
            $table->timestamps();

            $table->index('journal_entry_id');
            $table->index('account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('accounts');
    }
};
