<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $t) {
            if (!Schema::hasColumn('products', 'category'))    $t->string('category', 64)->nullable()->index();
            if (!Schema::hasColumn('products', 'image_url'))   $t->string('image_url', 255)->nullable();
            if (!Schema::hasColumn('products', 'description')) $t->text('description')->nullable();
            if (!Schema::hasColumn('products', 'is_popular'))  $t->boolean('is_popular')->default(false)->index();
            if (!Schema::hasColumn('products', 'type'))        $t->string('type', 32)->default('dish')->index(); // dish | ingredient
        });

        // Optional: backfill existing NULLs for 'type' to 'dish'
        if (Schema::hasColumn('products', 'type')) {
            DB::table('products')->whereNull('type')->update(['type' => 'dish']);
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $t) {
            // Drop columns if they exist (safe for repeated runs)
            if (Schema::hasColumn('products', 'is_popular'))  $t->dropColumn('is_popular');
            if (Schema::hasColumn('products', 'description')) $t->dropColumn('description');
            if (Schema::hasColumn('products', 'image_url'))   $t->dropColumn('image_url');
            if (Schema::hasColumn('products', 'category'))    $t->dropColumn('category');
            // Keep 'type' if you’ve started relying on it; drop only if you really want to roll it back:
            if (Schema::hasColumn('products', 'type'))        $t->dropColumn('type');
        });
    }
};
