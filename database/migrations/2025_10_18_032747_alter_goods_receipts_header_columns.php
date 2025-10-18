<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            // add if missing
            if (!Schema::hasColumn('goods_receipts', 'po_number')) {
                $table->string('po_number', 64)->nullable()->after('supplier_id');
            }
            if (!Schema::hasColumn('goods_receipts', 'location_id')) {
                $table->unsignedBigInteger('location_id')->nullable()->after('received_at');
            }
            if (!Schema::hasColumn('goods_receipts', 'supplier_id')) {
                $table->unsignedBigInteger('supplier_id')->nullable()->after('id');
            }
            // make purchase_order_id nullable if present & not already
            if (Schema::hasColumn('goods_receipts', 'purchase_order_id')) {
                $table->unsignedBigInteger('purchase_order_id')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            // no destructive down to avoid data loss
        });
    }
};
