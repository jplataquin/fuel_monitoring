<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetTypeController;
use App\Http\Controllers\ChargeableAccountController;
use App\Http\Controllers\FuelOrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UtilizationEntryController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'check_temp_password'])->name('dashboard');

Route::middleware(['auth', 'check_temp_password'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Reports
    Route::get('reports/asset-utilization', [ReportController::class, 'assetUtilization'])->name('reports.asset-utilization');
    Route::get('reports/fuel-orders', [ReportController::class, 'fuelOrdersSummary'])->name('reports.fuel-orders');
    Route::get('reports/chargeable-accounts', [ReportController::class, 'chargeableAccountSummary'])->name('reports.chargeable-accounts');

    // Assets
    Route::resource('assets', AssetController::class);
    Route::get('assets/{asset}/logs', [UtilizationEntryController::class, 'logs'])->name('assets.logs');
    Route::get('assets/{asset}/logs/print', [UtilizationEntryController::class, 'printLogs'])->name('assets.logs.print');

    // Utilization Entries
    Route::resource('utilization-entries', UtilizationEntryController::class)->except(['index']);

    // Fuel Orders
    Route::resource('fuel-orders', FuelOrderController::class)->except(['destroy']);
    Route::get('fuel-orders/{fuel_order}/actualize', [FuelOrderController::class, 'actualize'])->name('fuel-orders.actualize');
    Route::post('fuel-orders/{fuel_order}/actualize', [FuelOrderController::class, 'storeActualization'])->name('fuel-orders.store-actualization');

    // Admin, Moderator and Budgeteer routes for accounts
    Route::middleware('role:administrator,moderator,budgeteer')->group(function () {
        Route::resource('chargeable-accounts', ChargeableAccountController::class);
        Route::get('chargeable-accounts/{chargeable_account}/sub-accounts/json', [App\Http\Controllers\SubAccountController::class, 'byAccount'])->name('chargeable-accounts.sub-accounts.json');
        Route::get('sub-accounts/{sub_account}', [App\Http\Controllers\SubAccountController::class, 'show'])->name('sub-accounts.show');
        Route::get('sub-accounts/{sub_account}/edit', [App\Http\Controllers\SubAccountController::class, 'edit'])->name('sub-accounts.edit');
        Route::patch('sub-accounts/{sub_account}', [App\Http\Controllers\SubAccountController::class, 'update'])->name('sub-accounts.update');
        Route::post('chargeable-accounts/{chargeable_account}/sub-accounts', [App\Http\Controllers\SubAccountController::class, 'store'])->name('chargeable-accounts.sub-accounts.store');
        Route::delete('sub-accounts/{sub_account}', [App\Http\Controllers\SubAccountController::class, 'destroy'])->name('sub-accounts.destroy');
    });

    // Admin and Moderator only routes for budget approval
    Route::middleware('role:administrator,moderator')->group(function () {
        Route::patch('account-budgets/{account_budget}/approve', [App\Http\Controllers\SubAccountBudgetController::class, 'approve'])->name('account-budgets.approve');
        Route::patch('account-budgets/{account_budget}/reject', [App\Http\Controllers\SubAccountBudgetController::class, 'reject'])->name('account-budgets.reject');
        
        // User creation routes that are accessible to both Admin and Moderator
        // Note: These must be BEFORE the resource route to avoid wildcard conflict
        Route::get('users/create-data-logger', [UserController::class, 'createDataLogger'])->name('users.create-data-logger');
        Route::get('users/create-fuel-man', [UserController::class, 'createFuelMan'])->name('users.create-fuel-man');
        Route::get('users/create-budgeteer', [UserController::class, 'createBudgeteer'])->name('users.create-budgeteer');

        Route::resource('users', UserController::class)->except(['create', 'show']);
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    });

    // Admin, Moderator and Budgeteer routes
    Route::middleware('role:administrator,moderator,budgeteer')->group(function () {
        Route::resource('account-budgets', App\Http\Controllers\SubAccountBudgetController::class)->except(['create']);
    });

    // Admin only routes
    Route::middleware('role:administrator')->group(function () {
        Route::resource('asset-types', AssetTypeController::class);
        
        // Specific user creation for admins only
        Route::get('users/create-moderator', [UserController::class, 'createModerator'])->name('users.create-moderator');
        
        // Void Fuel Order
        Route::post('fuel-orders/{fuel_order}/void', [FuelOrderController::class, 'void'])->name('fuel-orders.void');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile/password', [ProfileController::class, 'editPassword'])->name('profile.edit_password');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update_password');
});

require __DIR__.'/auth.php';
