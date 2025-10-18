<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
public function up(): void {
Schema::create('payments', function (Blueprint $table) {
$table->id();
$table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
$table->enum('method', ['cash','card','transfer','wallet']);
$table->unsignedInteger('amount_cents');
$table->string('reference', 100)->nullable();
$table->enum('status', ['captured','void','refund'])->default('captured');
$table->dateTime('captured_at')->nullable();
$table->timestamps();
$table->index('order_id');
});
}
public function down(): void { Schema::dropIfExists('payments'); }
};