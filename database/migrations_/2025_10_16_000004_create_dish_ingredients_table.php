<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
public function up(): void {
Schema::create('dish_ingredients', function (Blueprint $table) {
$table->id();
$table->foreignId('dish_id')->constrained('dishes')->cascadeOnDelete();
$table->foreignId('product_id')->constrained('products');
$table->decimal('qty', 12, 4);
$table->string('unit', 16);
$table->decimal('yield_factor', 8, 4)->default(1.0000);
$table->timestamps();
$table->unique(['dish_id','product_id']);
});
}
public function down(): void { Schema::dropIfExists('dish_ingredients'); }
};