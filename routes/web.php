<?php

use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});



Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::view('/dashboard', 'dashboard')
        ->name('dashboard');
});

Route::middleware(['auth'])->prefix('inventory')->name('inventory.')->group(function () {

    Route::get('/', [InventoryController::class, 'index'])
        ->middleware('permission:stock.view')
        ->name('index');

    Route::get('/low-stock', [InventoryController::class, 'lowStock'])
        ->middleware('permission:stock.view')
        ->name('low-stock');

    Route::get('/transactions', [InventoryController::class, 'transactions'])
        ->middleware('permission:stock.view')
        ->name('transactions');

    Route::get('/stock-in', [InventoryController::class, 'stockInForm'])
        ->middleware('permission:stock.receive')
        ->name('stock-in');

    Route::post('/stock-in', [InventoryController::class, 'storeStockIn'])
        ->middleware('permission:stock.receive')
        ->name('stock-in.store');

    Route::get('/adjustment', [InventoryController::class, 'adjustmentForm'])
        ->middleware('permission:stock.adjust')
        ->name('adjustment');

    Route::post('/adjustment', [InventoryController::class, 'storeAdjustment'])
        ->middleware('permission:stock.adjust')
        ->name('adjustment.store');
});


Route::middleware(['auth'])->group(function () {
    Route::get('/products', [ProductController::class, 'index'])
        ->middleware('permission:products.view')
        ->name('products.index');

    Route::get('/products/create', [ProductController::class, 'create'])
        ->middleware('permission:products.create')
        ->name('products.create');

    Route::post('/products', [ProductController::class, 'store'])
        ->middleware('permission:products.create')
        ->name('products.store');

    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])
        ->middleware('permission:products.edit')
        ->name('products.edit');

    Route::put('/products/{product}', [ProductController::class, 'update'])
        ->middleware('permission:products.edit')
        ->name('products.update');

    Route::post('/categories/quick-create', [
        ProductController::class,
        'storeCategory',
    ])->middleware('permission:categories.manage')
        ->name('categories.quick-create');

    Route::post('/suppliers/quick-create', [
        ProductController::class,
        'storeSupplier',
    ])->middleware('permission:suppliers.manage')
        ->name('suppliers.quick-create');
});


Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('users', UserManagementController::class)
            ->except(['show', 'destroy']);
    });



Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/users', function () {
        return 'Admin user management';
    })->name('admin.users');
});

Route::middleware(['auth', 'permission:pos.access'])->group(function () {
    Route::get('/pos', function () {
        return 'POS screen';
    })->name('pos.index');
});

Route::middleware(['auth', 'permission:stock.receive'])->group(function () {
    Route::get('/inventory/receive', function () {
        return 'Receive stock';
    })->name('inventory.receive');
});

Route::middleware(['auth', 'permission:reports.financial'])->group(function () {
    Route::get('/reports/financial', function () {
        return 'Financial reports';
    })->name('reports.financial');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/pos', [PosController::class, 'index'])
        ->middleware('permission:pos.access')
        ->name('pos.index');

    Route::get('/pos/products/search', [PosController::class, 'searchProducts'])
        ->middleware('permission:pos.access')
        ->name('pos.products.search');

    Route::post('/pos/checkout', [PosController::class, 'checkout'])
        ->middleware(['permission:pos.access', 'permission:sales.create'])
        ->name('pos.checkout');
});


Route::middleware(['auth'])->group(function () {
    Route::resource('invoices', InvoiceController::class);


    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])
        ->middleware('permission:invoices.view')
        ->name('invoices.show');

    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])
        ->middleware('permission:invoices.print')
        ->name('invoices.pdf');

    Route::post('/invoices/{invoice}/payments', [
        InvoiceController::class,
        'recordPayment'
    ])
        ->middleware('permission:payments.record')
        ->name('invoices.payments.store');


    Route::post(
        '/invoices/quick-create-customer',
        [InvoiceController::class, 'quickStoreCustomer']
    )->name('invoices.customers.quick-create');

    Route::get('/payments/{payment}/receipt',
        [InvoiceController::class, 'paymentReceipt']
    )->name('payments.receipt');

    Route::get('/payments/{payment}/receipt/pdf',
        [InvoiceController::class, 'paymentReceiptPdf']
    )->name('payments.receipt.pdf');

    Route::resource('suppliers',SupplierController::class);
    Route::resource('categories',CategoryController::class);

});


