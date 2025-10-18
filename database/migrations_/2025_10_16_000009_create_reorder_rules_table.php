<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
public function up(): void {
Schema::create('reorder_rules', function (Blueprint $table) {
$table->id();
$table->foreignId('product_id')->constrained('products');
$table->foreignId('location_id')->constrained('locations');
$table->decimal('min_level', 14,4);
$table->decimal('reorder_to', 14,4)->nullable();
$table->unsignedBigInteger('supplier_id')->nullable();
$table->timestamps();
$table->unique(['product_id','location_id']);
});
}
public function down(): void { Schema::dropIfExists('reorder_rules'); }
};