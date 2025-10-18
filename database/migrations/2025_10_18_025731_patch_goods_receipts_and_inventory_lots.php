<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // goods_receipts
        Schema::table('goods_receipts', function (Blueprint $t) {
            if (!Schema::hasColumn('goods_receipts','supplier_id')) $t->unsignedBigInteger('supplier_id')->nullable()->after('id');
            if (!Schema::hasColumn('goods_receipts','po_number'))   $t->string('po_number',64)->nullable()->after('supplier_id');
            if (!Schema::hasColumn('goods_receipts','received_at')) $t->dateTime('received_at')->nullable()->after('po_number');
            if (!Schema::hasColumn('goods_receipts','location_id')) $t->unsignedBigInteger('location_id')->nullable()->after('received_at');
        });

        // inventory_lots (only if these might be missing)
        Schema::table('inventory_lots', function (Blueprint $t) {
            if (!Schema::hasColumn('inventory_lots','location_id'))     $t->unsignedBigInteger('location_id')->nullable()->after('product_id');
            if (!Schema::hasColumn('inventory_lots','unit_name'))       $t->string('unit_name',20)->nullable()->after('unit_cost_cents');
            if (!Schema::hasColumn('inventory_lots','lot_ref'))         $t->string('lot_ref',64)->nullable()->after('unit_name');
            if (!Schema::hasColumn('inventory_lots','received_at'))     $t->dateTime('received_at')->nullable()->after('lot_ref');
        });
    }

    public function down(): void
    {
        // keep it simple: don’t drop in down() to avoid data loss
    }
};
