<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Orders
        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->string('order_no', 30)->unique()->nullable();
                $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
                $table->enum('status', ['open','void','paid','refunded'])->default('open');
                $table->enum('channel', ['counter','delivery','pickup','internal'])->default('counter');
                $table->unsignedInteger('subtotal_cents')->default(0);
                $table->unsignedInteger('tax_cents')->default(0);
                $table->unsignedInteger('discount_cents')->default(0);
                $table->unsignedInteger('service_charge_cents')->default(0);
                $table->unsignedInteger('total_cents')->default(0);
                $table->unsignedInteger('paid_cents')->default(0);
                $table->unsignedInteger('change_cents')->default(0);
                $table->text('note')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();
            });
        }

        // Order Items  <<< THIS GUARD IS THE PART YOU'RE MISSING
        if (!Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->enum('item_type', ['dish','product']);
                $table->unsignedBigInteger('item_id');
                $table->string('name', 150);
                $table->decimal('qty', 12, 4);
                $table->unsignedInteger('unit_price_cents');
                $table->decimal('tax_rate', 5, 2)->default(0);
                $table->unsignedInteger('line_total_cents');
                $table->json('meta')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('order_items')) Schema::drop('order_items');
        if (Schema::hasTable('orders')) Schema::drop('orders');
    }
};
