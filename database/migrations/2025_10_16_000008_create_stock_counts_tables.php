<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
public function up(): void {
if (!Schema::hasTable('stock_counts')) {
    Schema::create('stock_counts', function (Blueprint $table) {
$table->id();
$table->foreignId('location_id')->constrained('locations');
$table->dateTime('counted_at');
$table->unsignedBigInteger('counted_by')->nullable();
$table->timestamps();
});
}

Schema::create('stock_count_lines', function (Blueprint $table) {
$table->id();
$table->foreignId('stock_count_id')->constrained('stock_counts')->cascadeOnDelete();
$table->foreignId('product_id')->constrained('products');
$table->decimal('expected_qty', 14,4);
$table->decimal('counted_qty', 14,4);
$table->decimal('variance_qty', 14,4);
});
}
public function down(): void {
Schema::dropIfExists('stock_count_lines');
Schema::dropIfExists('stock_counts');
}
};