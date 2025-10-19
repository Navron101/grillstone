<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderPaymentController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\GoodsReceiptController;
use App\Http\Controllers\Api\InventoryController; // ✅ ADD THIS

// ----- POS endpoints -----
Route::get('/products', [ProductController::class,'index']);
Route::post('/orders', [OrderController::class,'store']); // create + pay
Route::post('/orders/hold', [OrderController::class,'hold']);
Route::post('/orders/kitchen', [OrderController::class,'sendToKitchen']);
Route::post('/orders/{order}/pay', [OrderPaymentController::class, 'pay']);

// ----- Receive stock (GRN) -----
Route::post('/grn', [GoodsReceiptController::class, 'store']);
Route::post('/grn/upload', [GoodsReceiptController::class, 'uploadExcel']);
Route::get('/grn/template', [GoodsReceiptController::class, 'downloadTemplate']);

// ----- Stock reads -----
Route::get('/stock/summary', [StockController::class, 'summary']); // ?location_id=1
Route::get('/stock/low', [StockController::class, 'low']);         // ?location_id=1

// ----- Inventory CRUD -----
// Ingredients
Route::get('/inventory/ingredients', [InventoryController::class, 'listIngredients']);
Route::post('/inventory/ingredients', [InventoryController::class, 'createIngredient']);
Route::put('/inventory/ingredients/{id}', [InventoryController::class, 'updateIngredient']);
Route::delete('/inventory/ingredients/{id}', [InventoryController::class, 'deleteIngredient']);
Route::post('/inventory/ingredients/upload', [InventoryController::class, 'uploadIngredients']);
Route::get('/inventory/ingredients/template', [InventoryController::class, 'downloadIngredientsTemplate']);

// Dishes
Route::get('/inventory/dishes', [InventoryController::class, 'listDishes']);
Route::post('/inventory/dishes', [InventoryController::class, 'createDish']);
Route::put('/inventory/dishes/{id}', [InventoryController::class, 'updateDish']);
Route::delete('/inventory/dishes/{id}', [InventoryController::class, 'deleteDish']);

// Products (drinks, snacks, etc.)
Route::get('/inventory/products', [InventoryController::class, 'listProducts']);
Route::post('/inventory/products', [InventoryController::class, 'createProduct']);
Route::put('/inventory/products/{id}', [InventoryController::class, 'updateProduct']);
Route::delete('/inventory/products/{id}', [InventoryController::class, 'deleteProduct']);
Route::post('/inventory/products/upload', [InventoryController::class, 'uploadProducts']);
Route::get('/inventory/products/template', [InventoryController::class, 'downloadProductsTemplate']);

// Variants & components
Route::get('/inventory/dishes/{dishId}/variants', [InventoryController::class, 'listVariants']);
Route::post('/inventory/dishes/{dishId}/variants', [InventoryController::class, 'createVariant']);
Route::put('/inventory/dishes/{dishId}/variants/{variantId}', [InventoryController::class, 'updateVariant']);
Route::delete('/inventory/dishes/{dishId}/variants/{variantId}', [InventoryController::class, 'deleteVariant']);

Route::get('/inventory/variants/{variantId}/components', [InventoryController::class, 'listComponents']);
Route::post('/inventory/variants/{variantId}/components', [InventoryController::class, 'upsertComponent']);
Route::delete('/inventory/variants/{variantId}/components/{componentId}', [InventoryController::class, 'deleteComponent']);

// ----- Stocktakes -----
use App\Http\Controllers\Api\StocktakeController;

Route::get('/stocktakes', [StocktakeController::class, 'index']);
Route::post('/stocktakes', [StocktakeController::class, 'store']);
Route::get('/stocktakes/{id}', [StocktakeController::class, 'show']);
Route::put('/stocktakes/{id}/lines', [StocktakeController::class, 'updateLines']);
Route::post('/stocktakes/{id}/complete', [StocktakeController::class, 'complete']);
Route::post('/stocktakes/{id}/cancel', [StocktakeController::class, 'cancel']);
Route::delete('/stocktakes/{id}', [StocktakeController::class, 'destroy']);
Route::get('/stocktakes/{id}/variance-report', [StocktakeController::class, 'varianceReport']);
Route::get('/stocktakes/{id}/variance-report/download', [StocktakeController::class, 'downloadVarianceReport']);

// ----- Reports -----
use App\Http\Controllers\Api\ReportsController;

Route::get('/reports/dashboard', [ReportsController::class, 'dashboard']);
Route::get('/reports/trends', [ReportsController::class, 'salesTrends']);
