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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, number, boolean, json
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default tax rate
        DB::table('settings')->insert([
            [
                'key' => 'tax_rate',
                'value' => '15.00',
                'type' => 'number',
                'description' => 'Tax percentage applied to orders',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'tax_enabled',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable or disable tax on orders',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
