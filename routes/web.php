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




// Auth-only pages
Route::middleware('auth')->group(function () {
    // POS
    Route::get('/pos', function () {
        return Inertia::render('POS/Index', [
            'cashier'     => auth()->user()->name ?? 'Cashier',
            'tableNumber' => 1,
            'taxRate'     => 0.15,
        ]);
    })->name('pos.index');

    // Inventory (UI)
    Route::get('/inventory', fn () => Inertia::render('Inventory/Index'))->name('inventory.index');
    Route::get('/inventory/dishes', fn () => Inertia::render('Inventory/Dishes'))->name('inventory.dishes');
    Route::get('/inventory/grn', fn () => Inertia::render('Inventory/GRN'))->name('inventory.grn');
});

// Breeze / auth scaffolding (login, logout, etc.)
require __DIR__.'/auth.php';
