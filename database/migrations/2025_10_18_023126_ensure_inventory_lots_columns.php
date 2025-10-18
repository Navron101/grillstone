<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Create table if it doesn't exist
        if (! Schema::hasTable('inventory_lots')) {
            Schema::create('inventory_lots', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('product_id')->index();
                $t->unsignedBigInteger('location_id')->index();
                // on-hand qty for the lot (use decimal if you stock fractional units)
                $t->decimal('qty_on_hand', 14, 4)->default(0);
                $t->integer('unit_cost_cents')->default(0);
                $t->string('unit_name', 20)->nullable();   // e.g. g, kg, lb, L, etc.
                $t->string('lot_ref', 64)->nullable();     // ← this is the missing column
                $t->timestamp('received_at')->nullable();
                $t->timestamps();

                // (Optional) foreign keys if you want them strict
                // $t->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
                // $t->foreign('location_id')->references('id')->on('locations')->cascadeOnDelete();
            });
            return;
        }

        // Table exists: add any missing columns (safe re-run)
        Schema::table('inventory_lots', function (Blueprint $t) {
            if (! Schema::hasColumn('inventory_lots', 'qty_on_hand')) {
                $t->decimal('qty_on_hand', 14, 4)->default(0);
            }
            if (! Schema::hasColumn('inventory_lots', 'unit_cost_cents')) {
                $t->integer('unit_cost_cents')->default(0);
            }
            if (! Schema::hasColumn('inventory_lots', 'unit_name')) {
                $t->string('unit_name', 20)->nullable();
            }
            if (! Schema::hasColumn('inventory_lots', 'lot_ref')) {
                $t->string('lot_ref', 64)->nullable();   // ← add missing column
            }
            if (! Schema::hasColumn('inventory_lots', 'received_at')) {
                $t->timestamp('received_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Don't drop the whole table in down() for safety; just drop the added column
        if (Schema::hasTable('inventory_lots') && Schema::hasColumn('inventory_lots','lot_ref')) {
            Schema::table('inventory_lots', function (Blueprint $t) {
                $t->dropColumn('lot_ref');
            });
        }
    }
};
