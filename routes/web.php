<?php

use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Illuminate\Foundation\Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Products & Categories
    Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
    Route::resource('products', ProductController::class);
    Route::resource('categories', CategoryController::class)->except(['create', 'edit', 'show']);
    Route::resource('locations', WarehouseController::class)->except(['create', 'edit', 'show']);
    Route::resource('suppliers', SupplierController::class)->except(['create', 'edit', 'show']);
    
    // Inventory & Stock
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/movement', [InventoryController::class, 'recordMovement'])->name('inventory.movement');
    Route::put('/inventory/movement/{movement}', [InventoryController::class, 'updateMovement'])->name('inventory.update');
    Route::delete('/inventory/movement/{movement}', [InventoryController::class, 'destroyMovement'])->name('inventory.destroy');
    Route::get('/inventory/scanner', [InventoryController::class, 'scanner'])->name('inventory.scanner');
    Route::resource('lots', BatchController::class)
        ->parameters(['lots' => 'batch'])
        ->only(['index', 'store', 'update', 'destroy']);
    
    // Orders
    Route::get('/orders/{order}/pdf', [OrderController::class, 'downloadPdf'])->name('orders.pdf');
    Route::resource('orders', OrderController::class)->only(['index', 'store', 'update', 'destroy']);
    
    // Equipment & Assets
    Route::resource('assets', AssetController::class)->only(['index', 'store', 'update', 'destroy']);
    
    // Financial Transactions
    Route::resource('transactions', TransactionController::class)->only(['index', 'store', 'update', 'destroy']);

    // Monitoring (Available to everyone)
    Route::get('/monitoring/expiry', [MonitoringController::class, 'expiry'])->name('monitoring.expiry');
    Route::get('/monitoring/alerts', [MonitoringController::class, 'alerts'])->name('monitoring.alerts');
    Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');
    
    // Audits & Logs (Restricted)
    Route::middleware('role:Admin,Manager')->group(function () {
        Route::get('/audit-log', [AuditController::class, 'index'])->name('audit.index');
        Route::get('/stock-take', [InventoryController::class, 'stockTake'])->name('stock-take.index');
        Route::resource('users', UserController::class);
        
        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/stock-levels', [InventoryController::class, 'stockReport'])->name('reports.stock');
    });
});

require __DIR__.'/auth.php';
require __DIR__.'/settings.php';
