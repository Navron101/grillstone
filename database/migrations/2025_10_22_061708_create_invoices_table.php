<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->nullable();
            $table->string('supplier_name')->nullable();
            $table->date('invoice_date')->nullable();
            $table->string('original_filename');
            $table->string('file_path');
            $table->enum('status', ['pending', 'processed', 'approved', 'rejected'])->default('pending');
            $table->json('ocr_raw_data')->nullable(); // Raw OCR response
            $table->json('extracted_items')->nullable(); // Structured invoice items
            $table->unsignedBigInteger('total_amount_cents')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->unsignedBigInteger('goods_receipt_id')->nullable(); // Link to GRN if created
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('goods_receipt_id')->references('id')->on('goods_receipts')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
