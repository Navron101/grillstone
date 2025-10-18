<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            if (!Schema::hasColumn('goods_receipts', 'supplier_id')) {
                $table->unsignedBigInteger('supplier_id')->nullable()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            if (Schema::hasColumn('goods_receipts', 'supplier_id')) {
                $table->dropColumn('supplier_id');
            }
        });
    }
};
