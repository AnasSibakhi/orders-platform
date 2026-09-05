<?php

use App\Http\Controllers\Business\BusinessController;
use App\Http\Controllers\Business\ChannelController;
use App\Http\Controllers\Business\CustomerController;
use App\Http\Controllers\Business\DashboardController;
use App\Http\Controllers\Business\OrderController;
use App\Http\Controllers\Business\TeamController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('home') : redirect()->route('login');
});

Route::middleware(['auth', 'verified', 'active'])->group(function () {
    // Decides where a logged-in user lands: onboarding, their one
    // business's dashboard, or a switcher if they belong to several.
    Route::get('/home', HomeController::class)->name('home');

    Route::get('/onboarding/business', [BusinessController::class, 'create'])->name('onboarding.create');
    Route::post('/onboarding/business', [BusinessController::class, 'store'])->name('onboarding.store');

    Route::get('/businesses', [BusinessController::class, 'index'])->name('business.switch');

    // Every tenant-scoped route lives under /b/{business}/... and is
    // guarded by identify_business (resolves + verifies membership +
    // binds the tenant context used by the BelongsToBusiness scope).
    Route::prefix('b/{business}')->middleware('identify_business')->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('business.dashboard');

        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');

        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');

        Route::get('/channels', [ChannelController::class, 'index'])->name('channels.index');
        Route::post('/channels', [ChannelController::class, 'store'])->name('channels.store');

        Route::get('/team', [TeamController::class, 'index'])->name('team.index');

        // Only the business owner/manager can manage team membership.
        Route::middleware('business_role:owner,manager')->group(function () {
            Route::post('/team', [TeamController::class, 'store'])->name('team.store');
            Route::delete('/team/{member}', [TeamController::class, 'destroy'])->name('team.destroy');
        });
    });
});

require __DIR__.'/auth.php';
