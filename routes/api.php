<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\GoodsReceiptController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ComboController;
use App\Http\Controllers\Api\EmployeeTabController;
use App\Http\Controllers\Api\PayoutController;
use App\Http\Controllers\Api\TillSettlementController;
use App\Http\Controllers\Api\SettlementPdfController;

// ----- POS endpoints ----- (require POS module access)
Route::middleware(['auth', 'module:POS'])->group(function () {
    Route::get('/products', [ProductController::class,'index']);
    Route::get('/combos', [ComboController::class, 'index']); // Get combos for POS
    Route::post('/orders', [OrderController::class,'store']); // create + pay
    Route::post('/orders/hold', [OrderController::class,'hold']);
    Route::post('/orders/kitchen', [OrderController::class,'sendToKitchen']);
});

// ----- Inventory Module ----- (require Inventory module access)
Route::middleware(['auth', 'module:Inventory'])->group(function () {
    // Receive stock (GRN)
    Route::post('/grn', [GoodsReceiptController::class, 'store']);
    Route::post('/grn/upload', [GoodsReceiptController::class, 'uploadExcel']);
    Route::get('/grn/template', [GoodsReceiptController::class, 'downloadTemplate']);

    // Stock reads
    Route::get('/stock/summary', [StockController::class, 'summary']); // ?location_id=1
    Route::get('/stock/low', [StockController::class, 'low']);         // ?location_id=1

    // Categories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    // Inventory CRUD
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

    // Combos
    Route::get('/combos', [ComboController::class, 'index']);
    Route::get('/combos/{id}', [ComboController::class, 'show']);
    Route::post('/combos', [ComboController::class, 'store']);
    Route::put('/combos/{id}', [ComboController::class, 'update']);
    Route::delete('/combos/{id}', [ComboController::class, 'destroy']);
    Route::post('/combos/{id}/toggle-active', [ComboController::class, 'toggleActive']);
});

// ----- Stocktakes, Waste, Vendors, Purchase Orders, Invoices ----- (Inventory module)
use App\Http\Controllers\Api\StocktakeController;

Route::middleware(['auth', 'module:Inventory'])->group(function () {
    Route::get('/stocktakes', [StocktakeController::class, 'index']);
    Route::post('/stocktakes', [StocktakeController::class, 'store']);
    Route::get('/stocktakes/{id}', [StocktakeController::class, 'show']);
    Route::put('/stocktakes/{id}/lines', [StocktakeController::class, 'updateLines']);
    Route::post('/stocktakes/{id}/complete', [StocktakeController::class, 'complete']);
    Route::post('/stocktakes/{id}/cancel', [StocktakeController::class, 'cancel']);
    Route::delete('/stocktakes/{id}', [StocktakeController::class, 'destroy']);
    Route::get('/stocktakes/{id}/variance-report', [StocktakeController::class, 'varianceReport']);
    Route::get('/stocktakes/{id}/variance-report/download', [StocktakeController::class, 'downloadVarianceReport']);
});

// ----- Reports ----- (require Reports module access)
use App\Http\Controllers\Api\ReportsController;

Route::middleware(['auth', 'module:Reports'])->group(function () {
    Route::get('/reports/dashboard', [ReportsController::class, 'dashboard']);
    Route::get('/reports/trends', [ReportsController::class, 'salesTrends']);
});

// ----- HR & Payroll ----- (require HR module access)
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\TimeLogController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\ClockController;

Route::middleware(['auth', 'module:HR'])->group(function () {
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
});

// More HR routes (continuing HR module middleware)
use App\Http\Controllers\Api\EmploymentContractController;
use App\Http\Controllers\Api\JobLetterController;

Route::middleware(['auth', 'module:HR'])->group(function () {
    // Employment Contracts
    Route::get('/employment-contracts', [EmploymentContractController::class, 'index']);
    Route::get('/employment-contracts/{id}', [EmploymentContractController::class, 'show']);
    Route::post('/employment-contracts', [EmploymentContractController::class, 'store']);
    Route::delete('/employment-contracts/{id}', [EmploymentContractController::class, 'destroy']);
    Route::post('/employment-contracts/{id}/send-email', [EmploymentContractController::class, 'sendEmail']);
    Route::post('/employment-contracts/{id}/update-status', [EmploymentContractController::class, 'updateStatus']);
    Route::get('/employment-contracts/{id}/download-pdf', [EmploymentContractController::class, 'downloadPdf']);
    Route::get('/employment-contracts/{id}/download-word', [EmploymentContractController::class, 'downloadWord']);

    // Job Letters
    Route::get('/job-letters', [JobLetterController::class, 'index']);
    Route::get('/job-letters/search-employees', [JobLetterController::class, 'searchEmployees']);
    Route::get('/job-letters/{id}', [JobLetterController::class, 'show']);
    Route::post('/job-letters', [JobLetterController::class, 'store']);
    Route::delete('/job-letters/{id}', [JobLetterController::class, 'destroy']);
    Route::post('/job-letters/{id}/send-email', [JobLetterController::class, 'sendEmail']);
    Route::post('/job-letters/{id}/update-status', [JobLetterController::class, 'updateStatus']);
    Route::get('/job-letters/{id}/download-pdf', [JobLetterController::class, 'downloadPdf']);
    Route::get('/job-letters/{id}/download-word', [JobLetterController::class, 'downloadWord']);

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

    // Clock In/Out System
    Route::post('/clock/{employeeId}/in', [ClockController::class, 'clockIn']);
    Route::post('/clock/{employeeId}/out', [ClockController::class, 'clockOut']);
    Route::get('/clock/{employeeId}/status', [ClockController::class, 'currentStatus']);
    Route::get('/clock/{employeeId}/today', [ClockController::class, 'todayLogs']);
    Route::post('/clock/quick', [ClockController::class, 'quickClock']);
});

// ----- Finance Module ----- (Till Settlements and Payouts)
Route::middleware(['auth', 'module:Finance'])->group(function () {
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
});

// ----- Financial Reports ----- (require Finance module access)
use App\Http\Controllers\Api\FinancialReportController;

Route::middleware(['auth', 'module:Finance'])->group(function () {
    Route::get('/financial-reports/balance-sheet', [FinancialReportController::class, 'balanceSheet']);
    Route::get('/financial-reports/income-statement', [FinancialReportController::class, 'incomeStatement']);
    Route::get('/financial-reports/profit-and-loss', [FinancialReportController::class, 'profitAndLoss']);
    Route::get('/financial-reports/cash-flow', [FinancialReportController::class, 'cashFlowStatement']);
});

// ----- Invoice OCR ----- (require Inventory module access)
use App\Http\Controllers\Api\InvoiceOcrController;

Route::middleware(['auth', 'module:Inventory'])->group(function () {
    Route::post('/invoices/upload', [InvoiceOcrController::class, 'upload']);
    Route::get('/invoices', [InvoiceOcrController::class, 'index']);
    Route::get('/invoices/{invoice}', [InvoiceOcrController::class, 'show']);
    Route::put('/invoices/{invoice}', [InvoiceOcrController::class, 'update']);
    Route::delete('/invoices/{invoice}', [InvoiceOcrController::class, 'destroy']);
    Route::post('/invoices/{invoice}/create-grn', [InvoiceOcrController::class, 'createGoodsReceipt']);
    Route::get('/invoices/{invoice}/match-products', [InvoiceOcrController::class, 'matchProducts']);
});

// ----- Waste Management ----- (require Inventory module access)
use App\Http\Controllers\Api\WasteController;
use App\Http\Controllers\Api\WasteReportController;

Route::middleware(['auth', 'module:Inventory'])->group(function () {
    Route::get('/waste', [WasteController::class, 'index']);
    Route::post('/waste', [WasteController::class, 'store']);
    Route::get('/waste/{id}', [WasteController::class, 'show']);
    Route::put('/waste/{id}', [WasteController::class, 'update']);
    Route::delete('/waste/{id}', [WasteController::class, 'destroy']);

    // Waste Reports
    Route::get('/waste-reports/daily', [WasteReportController::class, 'daily']);
    Route::get('/waste-reports/trends', [WasteReportController::class, 'trends']);
    Route::get('/waste-reports/top-wasted', [WasteReportController::class, 'topWasted']);
    Route::get('/waste-reports/by-reason', [WasteReportController::class, 'byReason']);
    Route::get('/waste-reports/summary', [WasteReportController::class, 'summary']);
});

// ----- Settings ----- (require Settings module access)
use App\Http\Controllers\Api\SettingsController;

Route::middleware(['auth', 'module:Settings'])->group(function () {
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::get('/settings/{key}', [SettingsController::class, 'show']);
    Route::put('/settings/{key}', [SettingsController::class, 'update']);
    Route::post('/settings/bulk', [SettingsController::class, 'updateMultiple']);
    Route::post('/settings/signature/upload', [SettingsController::class, 'uploadSignature']);
    Route::delete('/settings/signature', [SettingsController::class, 'deleteSignature']);
});

// ----- Vendors & Purchase Orders ----- (require Inventory module access)
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\PurchaseOrderController;

Route::middleware(['auth', 'module:Inventory'])->group(function () {
    // Vendors
    Route::get('/vendors', [VendorController::class, 'index']);
    Route::get('/vendors/{id}', [VendorController::class, 'show']);
    Route::post('/vendors', [VendorController::class, 'store']);
    Route::put('/vendors/{id}', [VendorController::class, 'update']);
    Route::delete('/vendors/{id}', [VendorController::class, 'destroy']);

    // Purchase Orders
    Route::get('/purchase-orders', [PurchaseOrderController::class, 'index']);
    Route::get('/purchase-orders/{id}', [PurchaseOrderController::class, 'show']);
    Route::post('/purchase-orders', [PurchaseOrderController::class, 'store']);
    Route::put('/purchase-orders/{id}', [PurchaseOrderController::class, 'update']);
    Route::delete('/purchase-orders/{id}', [PurchaseOrderController::class, 'destroy']);
    Route::post('/purchase-orders/{id}/status', [PurchaseOrderController::class, 'changeStatus']);
});

// ----- Invoices (Manual) ----- (require Inventory module access)
use App\Http\Controllers\Api\InvoiceController;

Route::middleware(['auth', 'module:Inventory'])->group(function () {
    Route::get('/invoices-manual', [InvoiceController::class, 'index']);
    Route::get('/invoices-manual/{id}', [InvoiceController::class, 'show']);
    Route::post('/invoices-manual', [InvoiceController::class, 'store']);
    Route::put('/invoices-manual/{id}', [InvoiceController::class, 'update']);
    Route::delete('/invoices-manual/{id}', [InvoiceController::class, 'destroy']);
    Route::post('/invoices-manual/{id}/approve', [InvoiceController::class, 'approve']);
    Route::post('/invoices-manual/{id}/reject', [InvoiceController::class, 'reject']);
    Route::post('/invoices-manual/{id}/mark-paid', [InvoiceController::class, 'markAsPaid']);

    // OCR Invoice Processing
    Route::post('/invoices/upload-scan', [InvoiceController::class, 'uploadAndScan']);
    Route::get('/invoices/pending-review', [InvoiceController::class, 'pendingReview']);
    Route::post('/invoices/{id}/review-update', [InvoiceController::class, 'updateAfterReview']);
});

// ----- Expenses & Expense Categories ----- (require Finance module access)
use App\Http\Controllers\Api\ExpenseCategoryController;
use App\Http\Controllers\Api\ExpenseController;

Route::middleware(['auth', 'module:Finance'])->group(function () {
    // Expense Categories
    Route::get('/expense-categories', [ExpenseCategoryController::class, 'index']);
    Route::get('/expense-categories/{id}', [ExpenseCategoryController::class, 'show']);
    Route::post('/expense-categories', [ExpenseCategoryController::class, 'store']);
    Route::put('/expense-categories/{id}', [ExpenseCategoryController::class, 'update']);
    Route::delete('/expense-categories/{id}', [ExpenseCategoryController::class, 'destroy']);

    // Expenses
    Route::get('/expenses', [ExpenseController::class, 'index']);
    Route::get('/expenses/{id}', [ExpenseController::class, 'show']);
    Route::post('/expenses', [ExpenseController::class, 'store']);
    Route::put('/expenses/{id}', [ExpenseController::class, 'update']);
    Route::delete('/expenses/{id}', [ExpenseController::class, 'destroy']);
    Route::get('/expenses-report', [ExpenseController::class, 'report']);
});

// ----- Loyalty Program ----- (require Loyalty module access)
use App\Http\Controllers\Api\LoyaltyCompanyController;
use App\Http\Controllers\Api\LoyaltyEmployeeController;
use App\Http\Controllers\Api\LoyaltyController;
use App\Http\Controllers\Api\LoyaltySettlementController;

Route::middleware(['auth', 'module:Loyalty'])->group(function () {
    // Loyalty Companies
    Route::get('/loyalty/companies', [LoyaltyCompanyController::class, 'index']);
    Route::get('/loyalty/companies/{loyaltyCompany}', [LoyaltyCompanyController::class, 'show']);
    Route::post('/loyalty/companies', [LoyaltyCompanyController::class, 'store']);
    Route::put('/loyalty/companies/{loyaltyCompany}', [LoyaltyCompanyController::class, 'update']);
    Route::delete('/loyalty/companies/{loyaltyCompany}', [LoyaltyCompanyController::class, 'destroy']);

    // Loyalty Employees
    Route::get('/loyalty/employees', [LoyaltyEmployeeController::class, 'index']);
    Route::get('/loyalty/employees/{loyaltyEmployee}', [LoyaltyEmployeeController::class, 'show']);
    Route::post('/loyalty/employees', [LoyaltyEmployeeController::class, 'store']);
    Route::put('/loyalty/employees/{loyaltyEmployee}', [LoyaltyEmployeeController::class, 'update']);
    Route::delete('/loyalty/employees/{loyaltyEmployee}', [LoyaltyEmployeeController::class, 'destroy']);

    // POS Loyalty Operations
    Route::post('/loyalty/lookup', [LoyaltyController::class, 'lookup']);
    Route::post('/loyalty/calculate-discount', [LoyaltyController::class, 'calculateDiscount']);
    Route::post('/loyalty/apply-discount', [LoyaltyController::class, 'applyDiscount']);

    // Loyalty Settlements
    Route::get('/loyalty/settlements', [LoyaltySettlementController::class, 'index']);
    Route::get('/loyalty/settlements/pending-transactions', [LoyaltySettlementController::class, 'pendingTransactions']);
    Route::get('/loyalty/settlements/{loyaltySettlement}', [LoyaltySettlementController::class, 'show']);
    Route::get('/loyalty/settlements/{loyaltySettlement}/pdf', [LoyaltySettlementController::class, 'exportPdf']);
    Route::get('/loyalty/settlements/{loyaltySettlement}/excel', [LoyaltySettlementController::class, 'exportExcel']);
    Route::post('/loyalty/settlements/generate', [LoyaltySettlementController::class, 'generate']);
    Route::post('/loyalty/settlements/{loyaltySettlement}/finalize', [LoyaltySettlementController::class, 'finalize']);
    Route::post('/loyalty/settlements/{loyaltySettlement}/mark-sent', [LoyaltySettlementController::class, 'markAsSent']);
    Route::post('/loyalty/settlements/{loyaltySettlement}/record-payment', [LoyaltySettlementController::class, 'recordPayment']);
    Route::delete('/loyalty/settlements/{loyaltySettlement}', [LoyaltySettlementController::class, 'destroy']);
});

// ----- User Administration ----- (require Settings module access)
use App\Http\Controllers\Api\UserAdminController;

Route::middleware(['auth', 'module:Settings'])->group(function () {
    Route::get('/users', [UserAdminController::class, 'index']);
    Route::post('/users', [UserAdminController::class, 'store']);
    Route::put('/users/{user}', [UserAdminController::class, 'update']);
    Route::delete('/users/{user}', [UserAdminController::class, 'destroy']);

    Route::get('/roles', [UserAdminController::class, 'roles']);
    Route::put('/roles/{role}/permissions', [UserAdminController::class, 'updateRolePermissions']);
});
