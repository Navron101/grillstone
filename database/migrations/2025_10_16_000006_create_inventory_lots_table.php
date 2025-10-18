<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
public function up(): void {
if (!Schema::hasTable('inventory_lots')) {
    Schema::create('inventory_lots', function (Blueprint $table) {
$table->id();
$table->foreignId('product_id')->constrained('products');
$table->foreignId('location_id')->constrained('locations');
$table->decimal('qty_on_hand', 14, 4)->default(0);
$table->unsignedInteger('unit_cost_cents')->nullable();
$table->date('expires_at')->nullable();
$table->string('lot_code', 64)->nullable();
$table->timestamps();
$table->index(['product_id','location_id']);
});
}

}
public function down(): void { Schema::dropIfExists('inventory_lots'); }
};