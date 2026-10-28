<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\StockForecastController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

// ---------- Guest ----------
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.attempt');

    // Pendaftaran mandiri khusus pembeli (role customer)
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:10,1')->name('register.attempt');
});

// ---------- Harus login ----------
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Pembeli yang membuka /dashboard dialihkan ke katalog (lihat DashboardController)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ---- Khusus PEMBELI (dicek di server lewat Gate 'customer') ----
    Route::middleware('can:customer')->prefix('shop')->name('shop.')->group(function () {
        Route::get('/', [ShopController::class, 'index'])->name('index');
        Route::get('/order', [ShopController::class, 'create'])->name('order.create');
        Route::post('/order', [ShopController::class, 'store'])->name('order.store');
        Route::get('/orders', [ShopController::class, 'orders'])->name('orders.index');
        Route::get('/orders/{sale}', [ShopController::class, 'show'])->name('orders.show');
    });

    // ---- Khusus ADMIN & STAFF (Gate 'internal'): pembeli ditolak 403 ----
    Route::middleware('can:internal')->group(function () {

        // ---- Khusus ADMIN (dicek di server lewat Gate 'admin') ----
        Route::middleware('can:admin')->group(function () {
            // didaftarkan lebih dulu agar /products/create tidak tertangkap /products/{product}
            Route::resource('products', ProductController::class)->except(['index', 'show']);
            Route::resource('categories', CategoryController::class)->except('show');
            Route::resource('suppliers', SupplierController::class)->except('show');
            Route::resource('users', UserController::class)->except('show');

            Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');
            Route::post('/purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel');

            // Laporan laba (khusus admin)
            Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
        });

        // ---- Admin & Staff ----
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
        Route::get('/low-stock', [ProductController::class, 'lowStock'])->name('products.low-stock');
        Route::get('/stock-forecast', [StockForecastController::class, 'index'])->name('stock.forecast');

        Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show']);
        Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show']);
    });
});
