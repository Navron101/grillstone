<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('goods_receipts', function (Blueprint $t) {
            if (!Schema::hasColumn('goods_receipts', 'po_number')) {
                $t->string('po_number', 64)->nullable()->after('supplier_id');
            }
            // make sure we have a plain DATETIME (no timezone)
            if (!Schema::hasColumn('goods_receipts', 'received_at')) {
                $t->dateTime('received_at')->nullable()->after('po_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $t) {
            if (Schema::hasColumn('goods_receipts', 'po_number')) {
                $t->dropColumn('po_number');
            }
            // only drop if you really want to roll back fully
            // if (Schema::hasColumn('goods_receipts', 'received_at')) {
            //     $t->dropColumn('received_at');
            // }
        });
    }
};
