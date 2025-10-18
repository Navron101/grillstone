<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
public function up(): void {
Schema::create('purchase_orders', function (Blueprint $table) {
$table->id();
$table->foreignId('vendor_id')->constrained('vendors');
$table->enum('status', ['draft','sent','partial','received','cancelled'])->default('draft');
$table->dateTime('ordered_at')->nullable();
$table->dateTime('due_at')->nullable();
$table->string('reference', 100)->nullable();
$table->text('notes')->nullable();
$table->unsignedInteger('subtotal_cents')->default(0);
$table->unsignedInteger('tax_cents')->default(0);
$table->unsignedInteger('total_cents')->default(0);
$table->timestamps();
});
Schema::create('purchase_order_lines', function (Blueprint $table) {
$table->id();
$table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
$table->foreignId('product_id')->constrained('products');
$table->decimal('qty', 14,4);
$table->unsignedInteger('unit_cost_cents');
$table->unsignedInteger('line_total_cents');
});
}
public function down(): void {
Schema::dropIfExists('purchase_order_lines');
Schema::dropIfExists('purchase_orders');
}
};