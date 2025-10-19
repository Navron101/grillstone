<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the type column to support 'product' type
        // MySQL doesn't allow direct ALTER for varchar, so we'll just ensure the column exists
        // The type column already exists as varchar(255), so we just need to document the types:
        // - 'dish': Recipe-based dishes (composed of ingredients)
        // - 'ingredient': Raw materials used in recipes
        // - 'product': Finished products sold directly (drinks, snacks, etc.)

        // No schema change needed since type is already varchar(255)
        // Just ensure we have proper index
        if (!Schema::hasColumn('products', 'type')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('type')->default('dish')->after('updated_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No changes to revert
    }
};
