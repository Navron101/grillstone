<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // GOODS RECEIPTS
        if (Schema::hasTable('goods_receipts')) {
            // Add columns if missing
            Schema::table('goods_receipts', function (Blueprint $t) {
                if (!Schema::hasColumn('goods_receipts','received_at'))  $t->dateTime('received_at')->nullable()->index();
                if (!Schema::hasColumn('goods_receipts','location_id'))  $t->unsignedBigInteger('location_id')->nullable()->index();
                if (!Schema::hasColumn('goods_receipts','supplier_id'))  $t->unsignedBigInteger('supplier_id')->nullable()->index();
                if (!Schema::hasColumn('goods_receipts','po_number'))    $t->string('po_number',64)->nullable()->index();
                if (!Schema::hasColumn('goods_receipts','purchase_order_id')) $t->unsignedBigInteger('purchase_order_id')->nullable()->index();
            });

            // Make purchase_order_id NULLABLE if it exists but is NOT NULL
            if (Schema::hasColumn('goods_receipts','purchase_order_id')) {
                // If there is a FK, drop it to allow altering nullability (best-effort)
                try {
                    DB::statement('ALTER TABLE goods_receipts DROP FOREIGN KEY goods_receipts_purchase_order_id_foreign');
                } catch (\Throwable $e) {}
                try {
                    // MySQL/MariaDB compatible; make it nullable
                    DB::statement('ALTER TABLE goods_receipts MODIFY purchase_order_id BIGINT UNSIGNED NULL');
                } catch (\Throwable $e) {
                    // If doctrine/dbal is installed you could use ->change(), but raw SQL above covers it.
                }
            }
        }

        // INVENTORY LOTS (safe optional columns)
        if (Schema::hasTable('inventory_lots')) {
            Schema::table('inventory_lots', function (Blueprint $t) {
                if (!Schema::hasColumn('inventory_lots','unit_name')) $t->string('unit_name',20)->nullable();
                if (!Schema::hasColumn('inventory_lots','lot_ref'))   $t->string('lot_ref',64)->nullable()->index();
                if (!Schema::hasColumn('inventory_lots','received_at')) $t->dateTime('received_at')->nullable()->index();
                if (!Schema::hasColumn('inventory_lots','location_id')) $t->unsignedBigInteger('location_id')->nullable()->index();
            });
        }

        // OPTIONAL detail table
        if (!Schema::hasTable('goods_receipt_lines')) {
            Schema::create('goods_receipt_lines', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('goods_receipt_id')->nullable()->index();
                $t->unsignedBigInteger('inventory_lot_id')->nullable()->index();
                $t->unsignedBigInteger('product_id')->index();
                $t->decimal('qty', 16, 4)->default(0);
                $t->string('unit_name',20)->nullable();
                $t->integer('unit_cost_cents')->default(0);
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        // No destructive changes on down for safety.
    }
};
