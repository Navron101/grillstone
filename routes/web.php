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
    // Dashboard - redirect to POS for now
    Route::get('/dashboard', function () {
        return redirect()->route('pos.index');
    })->name('dashboard');

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
    Route::get('/inventory/categories', fn () => Inertia::render('Inventory/Categories'))->name('inventory.categories');
    Route::get('/inventory/grn', fn () => Inertia::render('Inventory/GRN'))->name('inventory.grn');
    Route::get('/inventory/invoice-upload', fn () => Inertia::render('Inventory/InvoiceUpload'))->name('inventory.invoice-upload');
    Route::get('/inventory/stocktake', fn () => Inertia::render('Inventory/Stocktake'))->name('inventory.stocktake');
    Route::get('/inventory/stocktake/{id}', fn ($id) => Inertia::render('Inventory/StocktakeDetail', ['id' => $id]))->name('inventory.stocktake.detail');

    // Reports
    Route::get('/reports', fn () => Inertia::render('Reports/Index'))->name('reports.index');
    Route::get('/reports/financial', fn () => Inertia::render('Reports/Financial'))->name('reports.financial');

    // HR & Payroll
    Route::get('/hr', fn () => Inertia::render('HR/Index'))->name('hr.index');
    Route::get('/hr/employees', fn () => Inertia::render('HR/Employees'))->name('hr.employees');
    Route::get('/hr/employees/{id}', fn ($id) => Inertia::render('HR/EmployeeDetail', ['id' => $id]))->name('hr.employee.detail');
    Route::get('/hr/departments', fn () => Inertia::render('HR/Departments'))->name('hr.departments');
    Route::get('/hr/time-logs', fn () => Inertia::render('HR/TimeLogs'))->name('hr.timelogs');
    Route::get('/hr/clock', fn () => Inertia::render('HR/Clock'))->name('hr.clock');
    Route::get('/payroll', fn () => Inertia::render('Payroll/Index'))->name('payroll.index');
    Route::get('/payroll/periods/{id}', fn ($id) => Inertia::render('Payroll/PeriodDetail', ['id' => $id]))->name('payroll.period.detail');

    // Till Settlements
    Route::get('/settlements', fn () => Inertia::render('Settlements/Index'))->name('settlements.index');
    Route::get('/settlements/{id}/print', fn ($id) => Inertia::render('Settlements/Print', ['settlementId' => $id]))->name('settlements.print');
});

// Breeze / auth scaffolding (login, logout, etc.)
require __DIR__.'/auth.php';
