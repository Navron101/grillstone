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
        // Add 'combo' and 'ingredient' to the item_type enum
        DB::statement("ALTER TABLE order_items MODIFY COLUMN item_type ENUM('dish', 'product', 'ingredient', 'combo') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'combo' and 'ingredient' from the item_type enum
        DB::statement("ALTER TABLE order_items MODIFY COLUMN item_type ENUM('dish', 'product') NOT NULL");
    }
};
