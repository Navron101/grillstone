<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('inventory_lots')) {
            Schema::create('inventory_lots', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('product_id')->index();
                $t->unsignedBigInteger('location_id')->index();
                $t->decimal('qty_on_hand', 14, 4)->default(0);
                $t->integer('unit_cost_cents')->default(0);
                $t->string('unit_name', 20)->nullable();
                $t->string('lot_ref', 64)->nullable();   // <- REQUIRED
                $t->timestamp('received_at')->nullable();
                $t->timestamps();
            });
            return;
        }

        Schema::table('inventory_lots', function (Blueprint $t) {
            if (!Schema::hasColumn('inventory_lots','qty_on_hand'))     $t->decimal('qty_on_hand',14,4)->default(0);
            if (!Schema::hasColumn('inventory_lots','unit_cost_cents')) $t->integer('unit_cost_cents')->default(0);
            if (!Schema::hasColumn('inventory_lots','unit_name'))       $t->string('unit_name',20)->nullable();
            if (!Schema::hasColumn('inventory_lots','lot_ref'))         $t->string('lot_ref',64)->nullable(); // <- add
            if (!Schema::hasColumn('inventory_lots','received_at'))     $t->timestamp('received_at')->nullable();
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('inventory_lots') && Schema::hasColumn('inventory_lots','lot_ref')) {
            Schema::table('inventory_lots', fn (Blueprint $t) => $t->dropColumn('lot_ref'));
        }
    }
};
