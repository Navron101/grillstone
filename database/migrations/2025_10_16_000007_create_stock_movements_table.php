<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
public function up(): void {
if (!Schema::hasTable('stock_movements')) {
    Schema::create('stock_movements', function (Blueprint $table) {
$table->id();
$table->foreignId('product_id')->constrained('products');
$table->foreignId('location_id')->constrained('locations');
$table->decimal('qty_delta', 14, 4);
$table->enum('reason', ['receive','sale','variance','transfer','waste','production']);
$table->string('ref_type', 50)->nullable();
$table->unsignedBigInteger('ref_id')->nullable();
$table->unsignedInteger('unit_cost_cents')->nullable();
$table->unsignedBigInteger('created_by')->nullable();
$table->timestamp('created_at')->useCurrent();
$table->index(['product_id','location_id','created_at']);
});
}

}
public function down(): void { Schema::dropIfExists('stock_movements'); }
};