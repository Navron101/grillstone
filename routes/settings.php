<?php

use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', '/settings/profile');

    // Profile and password settings - accessible to all authenticated users
    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');

    Route::put('settings/password', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('password.update');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/Appearance');
    })->name('appearance');

    // Settings module - requires Settings module access
    Route::middleware('module:Settings')->group(function () {
        Route::get('settings/tax', function () {
            return Inertia::render('settings/Tax');
        })->name('tax');

        Route::get('settings/hr', function () {
            return Inertia::render('settings/HR');
        })->name('hr');

        // User Administration (Admin & Director only)
        Route::get('settings/users', function () {
            return Inertia::render('settings/Users');
        })->name('users');
    });
});
