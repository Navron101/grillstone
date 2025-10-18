<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('goods_receipt_lines', function (Blueprint $table) {
            // Add inventory_lot_id if missing (nullable FK to inventory_lots.id)
            if (!Schema::hasColumn('goods_receipt_lines', 'inventory_lot_id')) {
                $table->unsignedBigInteger('inventory_lot_id')->nullable()->after('goods_receipt_id');
                $table->foreign('inventory_lot_id')
                      ->references('id')->on('inventory_lots')
                      ->onDelete('set null');
            }

            // Add timestamps if missing
            if (!Schema::hasColumn('goods_receipt_lines', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('goods_receipt_lines', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipt_lines', function (Blueprint $table) {
            if (Schema::hasColumn('goods_receipt_lines', 'inventory_lot_id')) {
                $table->dropForeign(['inventory_lot_id']);
                $table->dropColumn('inventory_lot_id');
            }
            if (Schema::hasColumn('goods_receipt_lines', 'created_at')) {
                $table->dropColumn('created_at');
            }
            if (Schema::hasColumn('goods_receipt_lines', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });
    }
};
