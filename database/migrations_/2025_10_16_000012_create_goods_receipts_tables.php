<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
public function up(): void {
Schema::create('goods_receipts', function (Blueprint $table) {
$table->id();
$table->foreignId('purchase_order_id')->constrained('purchase_orders');
$table->dateTime('received_at');
$table->unsignedBigInteger('received_by')->nullable();
$table->string('reference', 100)->nullable();
$table->timestamps();
});
Schema::create('goods_receipt_lines', function (Blueprint $table) {
$table->id();
$table->foreignId('goods_receipt_id')->constrained('goods_receipts')->cascadeOnDelete();
$table->foreignId('product_id')->constrained('products');
$table->decimal('qty', 14,4);
$table->unsignedInteger('unit_cost_cents');
});
}
public function down(): void {
Schema::dropIfExists('goods_receipt_lines');
Schema::dropIfExists('goods_receipts');
}
};