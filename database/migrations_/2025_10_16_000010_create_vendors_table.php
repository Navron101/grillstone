<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
public function up(): void {
Schema::create('vendors', function (Blueprint $table) {
$table->id();
$table->string('name', 150);
$table->string('contact_name', 100)->nullable();
$table->string('phone', 50)->nullable();
$table->string('email', 120)->nullable();
$table->text('notes')->nullable();
$table->timestamps();
});
}
public function down(): void { Schema::dropIfExists('vendors'); }
};