<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('inventory_lots', function (Blueprint $t) {
            if (!Schema::hasColumn('inventory_lots', 'lot_ref')) {
                $t->string('lot_ref', 64)->nullable()->after('unit_cost_cents');
            }
            // ensure received_at exists and is DATETIME (not timestamp with TZ)
            if (!Schema::hasColumn('inventory_lots', 'received_at')) {
                $t->dateTime('received_at')->nullable()->after('lot_ref');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventory_lots', function (Blueprint $t) {
            if (Schema::hasColumn('inventory_lots', 'lot_ref')) {
                $t->dropColumn('lot_ref');
            }
            // optional rollback for received_at
            // if (Schema::hasColumn('inventory_lots', 'received_at')) {
            //     $t->dropColumn('received_at');
            // }
        });
    }
};
