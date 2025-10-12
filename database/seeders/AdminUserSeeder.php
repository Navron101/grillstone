<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrNew(['email' => 'admin@admin.com']);
        $user->name ??= 'Admin';
        $user->username ??= 'admin';

        // Set/refresh password to a known default if missing (or keep if already set).
        if (!$user->exists || !$user->password) {
            $user->password = Hash::make('password'); // change after first login!
        }

        $user->save();
    }
}
