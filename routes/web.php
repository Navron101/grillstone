<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Landing: send guests to login, logged-in users to POS
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('pos.index')
        : redirect()->route('login');
})->name('home');

// POS (auth only — no email verification)
Route::middleware('auth')->group(function () {
    Route::get('/pos', function () {
        return Inertia::render('POS/Index', [
            'cashier'     => auth()->user()->name ?? 'Cashier',
            'tableNumber' => 1,
            'taxRate'     => 0.15,
        ]);
    })->name('pos.index');
});

// Keep Breeze's auth routes (login, logout, register, password, etc.)
require __DIR__.'/auth.php';
