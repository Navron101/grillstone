<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 🔸 Create or update the admin user
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('password'),
            ]
        );

        // 🔸 Create or update the cashier user
        User::updateOrCreate(
            ['username' => 'cashier'],
            [
                'name' => 'Cashier',
                'username' => 'cashier',
                'email' => 'cashier@example.com',
                'password' => Hash::make('password'),
            ]
        );

        // 🔸 Call other seeders
        $this->call([
            JamaicanRestaurantSeeder::class,
        ]);
    }
}
