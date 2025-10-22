<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\GoodsReceiptController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\EmployeeTabController;
use App\Http\Controllers\Api\PayoutController;
use App\Http\Controllers\Api\TillSettlementController;
use App\Http\Controllers\Api\SettlementPdfController;

// ----- POS endpoints -----
Route::get('/products', [ProductController::class,'index']);
Route::post('/orders', [OrderController::class,'store']); // create + pay
Route::post('/orders/hold', [OrderController::class,'hold']);
Route::post('/orders/kitchen', [OrderController::class,'sendToKitchen']);

// ----- Receive stock (GRN) -----
Route::post('/grn', [GoodsReceiptController::class, 'store']);
Route::post('/grn/upload', [GoodsReceiptController::class, 'uploadExcel']);
Route::get('/grn/template', [GoodsReceiptController::class, 'downloadTemplate']);

// ----- Stock reads -----
Route::get('/stock/summary', [StockController::class, 'summary']); // ?location_id=1
Route::get('/stock/low', [StockController::class, 'low']);         // ?location_id=1

// ----- Categories -----
Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/categories', [CategoryController::class, 'store']);
Route::put('/categories/{id}', [CategoryController::class, 'update']);
Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

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

// Product/Dish Image Management
Route::post('/inventory/products/{id}/image', [InventoryController::class, 'uploadImage']);
Route::put('/inventory/products/{id}/image-url', [InventoryController::class, 'updateImageUrl']);
Route::delete('/inventory/products/{id}/image', [InventoryController::class, 'deleteImage']);

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

// ----- HR & Payroll -----
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\TimeLogController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\ClockController;

// Departments
Route::get('/departments', [DepartmentController::class, 'index']);
Route::post('/departments', [DepartmentController::class, 'store']);
Route::put('/departments/{id}', [DepartmentController::class, 'update']);
Route::delete('/departments/{id}', [DepartmentController::class, 'destroy']);

// Employees
Route::get('/employees', [EmployeeController::class, 'index']);
Route::get('/employees/{id}', [EmployeeController::class, 'show']);
Route::post('/employees', [EmployeeController::class, 'store']);
Route::put('/employees/{id}', [EmployeeController::class, 'update']);
Route::delete('/employees/{id}', [EmployeeController::class, 'destroy']);

// Time Logs
Route::get('/time-logs', [TimeLogController::class, 'index']);
Route::post('/time-logs', [TimeLogController::class, 'store']);
Route::put('/time-logs/{id}', [TimeLogController::class, 'update']);
Route::delete('/time-logs/{id}', [TimeLogController::class, 'destroy']);
Route::post('/time-logs/{id}/approve', [TimeLogController::class, 'approve']);
Route::post('/time-logs/{id}/reject', [TimeLogController::class, 'reject']);
Route::post('/time-logs/bulk-approve', [TimeLogController::class, 'bulkApprove']);

// Payroll
Route::get('/payroll/periods', [PayrollController::class, 'periods']);
Route::get('/payroll/periods/{id}', [PayrollController::class, 'showPeriod']);
Route::post('/payroll/periods', [PayrollController::class, 'createPeriod']);
Route::post('/payroll/periods/{id}/generate', [PayrollController::class, 'generatePayslips']);
Route::post('/payroll/periods/{id}/approve', [PayrollController::class, 'approvePeriod']);
Route::post('/payroll/periods/{id}/email', [PayrollController::class, 'emailPayslips']);
Route::post('/payroll/periods/{id}/email-pdf', [PayrollController::class, 'emailPayslipsPdf']);
Route::post('/payroll/periods/{id}/mark-paid', [PayrollController::class, 'markPaid']);
Route::get('/payroll/periods/{periodId}/payslips', [PayrollController::class, 'payslips']);
Route::get('/payroll/periods/{periodId}/payslips/{payslipId}', [PayrollController::class, 'showPayslip']);
Route::put('/payroll/periods/{periodId}/payslips/{payslipId}', [PayrollController::class, 'updatePayslip']);
Route::get('/payroll/periods/{periodId}/payslips/{payslipId}/pdf', [PayrollController::class, 'downloadPayslipPdf']);
Route::post('/payroll/periods/{periodId}/payslips/{payslipId}/email-pdf', [PayrollController::class, 'emailSinglePayslipPdf']);

// Employee Tab (charges)
Route::get('/employee-tab', [EmployeeTabController::class, 'index']);
Route::get('/employee-tab/pending/{employeeId}', [EmployeeTabController::class, 'pending']);
Route::get('/employee-tab/balance/{employeeId}', [EmployeeTabController::class, 'balance']);
Route::post('/employee-tab', [EmployeeTabController::class, 'store']);
Route::put('/employee-tab/{id}', [EmployeeTabController::class, 'update']);
Route::delete('/employee-tab/{id}', [EmployeeTabController::class, 'destroy']);

// Payouts
Route::get('/payouts', [PayoutController::class, 'index']);
Route::post('/payouts', [PayoutController::class, 'store']);
Route::get('/payouts/today-total', [PayoutController::class, 'todayTotal']);

// Till Settlements
Route::get('/settlements', [TillSettlementController::class, 'index']);
Route::get('/settlements/calculate', [TillSettlementController::class, 'calculate']);
Route::post('/settlements', [TillSettlementController::class, 'store']);
Route::get('/settlements/{id}', [TillSettlementController::class, 'show']);
Route::get('/settlements/{id}/pdf', [SettlementPdfController::class, 'download']);

// Clock In/Out System
Route::post('/clock/{employeeId}/in', [ClockController::class, 'clockIn']);
Route::post('/clock/{employeeId}/out', [ClockController::class, 'clockOut']);
Route::get('/clock/{employeeId}/status', [ClockController::class, 'currentStatus']);
Route::get('/clock/{employeeId}/today', [ClockController::class, 'todayLogs']);
Route::post('/clock/quick', [ClockController::class, 'quickClock']);

// ----- Financial Reports -----
use App\Http\Controllers\Api\FinancialReportController;

Route::get('/financial-reports/balance-sheet', [FinancialReportController::class, 'balanceSheet']);
Route::get('/financial-reports/income-statement', [FinancialReportController::class, 'incomeStatement']);
Route::get('/financial-reports/profit-and-loss', [FinancialReportController::class, 'profitAndLoss']);
Route::get('/financial-reports/cash-flow', [FinancialReportController::class, 'cashFlowStatement']);
