<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED

            // Must match parents' BIGINT UNSIGNED ids:
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            $table->unsignedInteger('qty');
            $table->unsignedInteger('price_cents');       // unit price at time of sale
            $table->unsignedInteger('line_total_cents');  // qty * price_cents
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
