<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('goods_receipt_lines')) {
            Schema::table('goods_receipt_lines', function (Blueprint $table) {
                if (!Schema::hasColumn('goods_receipt_lines', 'inventory_lot_id')) {
                    $table->foreignId('inventory_lot_id')->nullable()->after('goods_receipt_id')->constrained('inventory_lots')->nullOnDelete();
                }
                if (!Schema::hasColumn('goods_receipt_lines', 'created_at')) {
                    $table->timestamps();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('goods_receipt_lines')) {
            Schema::table('goods_receipt_lines', function (Blueprint $table) {
                if (Schema::hasColumn('goods_receipt_lines', 'inventory_lot_id')) {
                    $table->dropForeign(['inventory_lot_id']);
                    $table->dropColumn('inventory_lot_id');
                }
                if (Schema::hasColumn('goods_receipt_lines', 'created_at')) {
                    $table->dropTimestamps();
                }
            });
        }
    }
};
