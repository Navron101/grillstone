<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('goods_receipt_lines', function (Blueprint $table) {
            if (!Schema::hasColumn('goods_receipt_lines', 'inventory_lot_id')) {
                $table->unsignedBigInteger('inventory_lot_id')->nullable()->after('goods_receipt_id');
                // add FK if inventory_lots exists
                if (Schema::hasTable('inventory_lots')) {
                    $table->foreign('inventory_lot_id')
                          ->references('id')->on('inventory_lots')
                          ->cascadeOnUpdate()
                          ->nullOnDelete();
                }
            }
            if (!Schema::hasColumn('goods_receipt_lines', 'unit_name')) {
                $table->string('unit_name', 20)->nullable()->after('qty');
            }
            // optional: ensure goods_receipt_id exists
            if (!Schema::hasColumn('goods_receipt_lines', 'goods_receipt_id')) {
                $table->unsignedBigInteger('goods_receipt_id')->nullable()->first();
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
            if (Schema::hasColumn('goods_receipt_lines', 'unit_name')) {
                $table->dropColumn('unit_name');
            }
        });
    }
};
