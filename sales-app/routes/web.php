<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected routes
Route::middleware(['auth'])->group(function () {
    
    // Dashboard - accessible by all authenticated users
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Product routes - only admin can create/edit/delete
    Route::resource('products', ProductController::class)->except(['create', 'store', 'edit', 'update', 'destroy']);
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });
    
    // Transaction routes - admin and kasir can manage transactions
    Route::middleware(['role:admin,kasir'])->group(function () {
        Route::resource('transactions', TransactionController::class);
    });
    
    // Reports routes - only admin can access reports
    Route::middleware(['role:admin'])->prefix('reports')->name('reports.')->group(function () {
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/purchases', [ReportController::class, 'purchases'])->name('purchases');
        Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory');
        Route::get('/profit', [ReportController::class, 'profit'])->name('profit');
    });
    
    // Additional transaction routes for specific transaction types
    Route::middleware(['role:admin,kasir'])->group(function () {
        Route::get('/sales', function () {
            return redirect()->route('transactions.create', ['jenis' => 'penjualan']);
        })->name('sales.create');
        
        Route::get('/purchases', function () {
            return redirect()->route('transactions.create', ['jenis' => 'pembelian']);
        })->name('purchases.create');
    });
    
    // Purchase transactions - only admin can create purchases
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/purchases/create', function () {
            return app(TransactionController::class)->create(request()->merge(['jenis' => 'pembelian']));
        })->name('purchases.create');
    });
});
