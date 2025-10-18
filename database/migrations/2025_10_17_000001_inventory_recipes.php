<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Products: add type + unit (for ingredients)
        Schema::table('products', function (Blueprint $t) {
            if (!Schema::hasColumn('products', 'type')) {
                $t->string('type')->default('dish'); // 'dish' | 'ingredient'
            }
            if (!Schema::hasColumn('products', 'unit_name')) {
                $t->string('unit_name')->nullable(); // e.g., 'lb', 'kg', 'g', 'pcs'
            }
        });

        // Recipe variants: a dish can have 1+ variants (sizes/recipes)
        Schema::create('recipe_variants', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained('products')->cascadeOnDelete(); // dish product
            $t->string('name');          // e.g., "Regular", "Large", "Premium"
            $t->boolean('is_default')->default(false);
            $t->timestamps();
        });

        // Recipe components: ingredient usage per 1 dish unit
        Schema::create('recipe_components', function (Blueprint $t) {
            $t->id();
            $t->foreignId('variant_id')->constrained('recipe_variants')->cascadeOnDelete();
            $t->foreignId('ingredient_product_id')->constrained('products')->cascadeOnDelete();
            $t->decimal('qty_per_unit', 12, 4); // e.g., 1.0000 lb
            $t->string('unit_name')->nullable(); // display/help; not used in arithmetic
            $t->timestamps();
            $t->unique(['variant_id','ingredient_product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_components');
        Schema::dropIfExists('recipe_variants');
        Schema::table('products', function (Blueprint $t) {
            if (Schema::hasColumn('products', 'type')) $t->dropColumn('type');
            if (Schema::hasColumn('products', 'unit_name')) $t->dropColumn('unit_name');
        });
    }
};
