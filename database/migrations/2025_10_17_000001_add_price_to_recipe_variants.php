<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('recipe_variants', function (Blueprint $t) {
            if (!Schema::hasColumn('recipe_variants','price_cents')) {
                $t->integer('price_cents')->nullable()->after('name');
            }
        });
    }
    public function down(): void {
        Schema::table('recipe_variants', function (Blueprint $t) {
            if (Schema::hasColumn('recipe_variants','price_cents')) {
                $t->dropColumn('price_cents');
            }
        });
    }
};
