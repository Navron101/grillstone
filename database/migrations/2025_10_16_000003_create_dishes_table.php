<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
public function up(): void {
if (!Schema::hasTable('dishes')) {
    Schema::create('dishes', function (Blueprint $table) {
$table->id();
$table->string('name', 150);
$table->string('sku', 64)->nullable()->unique();
$table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
$table->unsignedInteger('price_cents')->default(0);
$table->decimal('tax_rate', 5,2)->default(0);
$table->boolean('is_active')->default(true);
$table->string('image_url', 255)->nullable();
$table->text('notes')->nullable();
$table->timestamps();
});
}

}
public function down(): void { Schema::dropIfExists('dishes'); }
};