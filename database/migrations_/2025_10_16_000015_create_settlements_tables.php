<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
public function up(): void {
Schema::create('settlements', function (Blueprint $table) {
$table->id();
$table->dateTime('opened_at');
$table->dateTime('closed_at')->nullable();
$table->unsignedBigInteger('opened_by')->nullable();
$table->unsignedBigInteger('closed_by')->nullable();
$table->unsignedInteger('opening_float_cents')->default(0);
$table->unsignedInteger('closing_cash_cents')->nullable();
$table->text('note')->nullable();
$table->timestamps();
});
Schema::create('settlement_payments', function (Blueprint $table) {
$table->id();
$table->foreignId('settlement_id')->constrained('settlements')->cascadeOnDelete();
$table->enum('method', ['cash','card','transfer','wallet']);
$table->unsignedInteger('amount_cents');
$table->unsignedInteger('count')->default(0);
});
}
public function down(): void {
Schema::dropIfExists('settlement_payments');
Schema::dropIfExists('settlements');
}
};