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
        Schema::create('stocktakes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->string('reference')->nullable(); // e.g., "ST-2025-001"
            $table->enum('status', ['draft', 'completed', 'cancelled'])->default('draft');
            $table->timestamp('counted_at')->nullable();
            $table->foreignId('counted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('stocktake_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stocktake_id')->constrained('stocktakes')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('system_qty', 12, 4)->default(0); // What the system says
            $table->decimal('actual_qty', 12, 4)->nullable();  // What was actually counted
            $table->decimal('variance', 12, 4)->nullable();    // actual - system
            $table->integer('unit_cost_cents')->nullable();    // For variance value calculation
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['stocktake_id', 'product_id']); // One line per product per stocktake
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocktake_lines');
        Schema::dropIfExists('stocktakes');
    }
};
