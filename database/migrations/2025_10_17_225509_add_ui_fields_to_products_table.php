<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'type')) {
                $table->string('type', 32)->default('dish')->index(); // or 'ingredient'
            }
            if (!Schema::hasColumn('products', 'category')) {
                $table->string('category', 64)->nullable()->index();
            }
            if (!Schema::hasColumn('products', 'image_url')) {
                $table->string('image_url', 255)->nullable();
            }
            if (!Schema::hasColumn('products', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('products', 'is_popular')) {
                $table->boolean('is_popular')->default(false)->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // drop in reverse if you want
            if (Schema::hasColumn('products', 'is_popular')) $table->dropColumn('is_popular');
            if (Schema::hasColumn('products', 'description')) $table->dropColumn('description');
            if (Schema::hasColumn('products', 'image_url')) $table->dropColumn('image_url');
            if (Schema::hasColumn('products', 'category')) $table->dropColumn('category');
            // keep 'type' if you’re already using it; otherwise you can drop it too
        });
    }
};
