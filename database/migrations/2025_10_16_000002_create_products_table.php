<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
public function up(): void {
if (!Schema::hasTable('products')) {
    Schema::create('products', function (Blueprint $table) {
$table->id();
$table->string('name', 150);
$table->string('sku', 64)->nullable()->unique();
$table->string('barcode', 64)->nullable()->unique();
$table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
$table->string('unit', 16)->default('ea');
$table->boolean('is_stock_item')->default(true);
$table->boolean('is_active')->default(true);
$table->unsignedInteger('price_cents')->default(0);
$table->decimal('tax_rate', 5, 2)->default(0);
$table->unsignedInteger('cost_cents')->nullable();
$table->timestamps();
});
}

}
public function down(): void { Schema::dropIfExists('products'); }
};