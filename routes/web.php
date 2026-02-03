<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

use App\Livewire\ClientIndex;
use App\Livewire\ClientForm;

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/clients', ClientIndex::class)->name('clients.index');
    Route::get('/clients/create', ClientForm::class)->name('clients.create');
    Route::get('/clients/{client}', \App\Livewire\ClientDetail::class)->name('clients.show');
    Route::get('/clients/{client}/edit', ClientForm::class)->name('clients.edit');

    Route::get('/products', \App\Livewire\ProductIndex::class)->name('products.index');

    Route::middleware(['role:admin'])->group(function () {
        Route::get('/products/create', \App\Livewire\ProductForm::class)->name('products.create');
        Route::get('/products/{product}/edit', \App\Livewire\ProductForm::class)->name('products.edit');

        // User Management
        Route::get('/users', \App\Livewire\UserIndex::class)->name('users.index');
        Route::get('/users/create', \App\Livewire\UserForm::class)->name('users.create');
        Route::get('/users/{user}/edit', \App\Livewire\UserForm::class)->name('users.edit');

        // Purchases & Providers (Admin Only)
        Route::get('/purchases', \App\Livewire\PurchaseIndex::class)->name('purchases.index');
        Route::get('/purchases/create', \App\Livewire\PurchaseForm::class)->name('purchases.create');
        Route::get('/providers', \App\Livewire\ProviderIndex::class)->name('providers.index');
    });

    // Sales Routes
    Route::get('/sales/create', \App\Livewire\SaleForm::class)->name('sales.create');
    Route::get('/sales/{sale}', \App\Livewire\SaleDetail::class)->name('sales.show');

    // Expenses Routes (Global access, but logic inside handles filtering)
    Route::get('/expenses', \App\Livewire\ExpenseIndex::class)->name('expenses.index');
    Route::get('/expenses/create', \App\Livewire\ExpenseForm::class)->name('expenses.create');
    Route::get('/expenses/{expense}/edit', \App\Livewire\ExpenseForm::class)->name('expenses.edit');

    // Liquidations Routes
    Route::get('/liquidations', \App\Livewire\LiquidationIndex::class)->name('liquidations.index');
    Route::get('/liquidations/create', \App\Livewire\LiquidationForm::class)->name('liquidations.create');
    Route::get('/liquidations/{liquidation}', \App\Livewire\LiquidationForm::class)->name('liquidations.show');
    Route::get('/liquidations/{liquidation}/pdf', [\App\Http\Controllers\LiquidationController::class, 'downloadPDF'])->name('liquidations.pdf');

    // Reports Routes (Admin only)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/reports', \App\Livewire\Reports\ReportIndex::class)->name('reports.index');
        Route::get('/reports/returns', \App\Livewire\Reports\ReturnReport::class)->name('reports.returns');
        Route::get('/reports/accounts-payable', \App\Livewire\Reports\AccountsPayableReport::class)->name('reports.accounts-payable');
        Route::get('/reports/accounts-payable/pdf', [\App\Http\Controllers\ReportController::class, 'accountsPayablePDF'])->name('reports.accounts-payable.pdf');
        Route::get('/reports/finance', \App\Livewire\Reports\SalesProfitabilityReport::class)->name('reports.finance');
    });
});

require __DIR__ . '/auth.php';