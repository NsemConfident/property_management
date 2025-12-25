<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Volt::route('dashboard', 'dashboard.index')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    // Role-based dashboard routes
    Volt::route('dashboard/tenant', 'dashboard.tenant')
        ->middleware(['role:tenant'])
        ->name('dashboard.tenant');
    
    Volt::route('dashboard/owner', 'dashboard.owner')
        ->middleware(['role:owner'])
        ->name('dashboard.owner');
    
    Volt::route('dashboard/manager', 'dashboard.manager')
        ->middleware(['role:manager'])
        ->name('dashboard.manager');

    // Property Management Routes (Owner & Manager)
    Volt::route('properties', 'properties.index')
        ->middleware(['role:owner,manager'])
        ->name('properties.index');
    
    Volt::route('properties/create', 'properties.create')
        ->middleware(['role:owner,manager'])
        ->name('properties.create');
    
    Volt::route('properties/{property}', 'properties.show')
        ->middleware(['role:owner,manager'])
        ->name('properties.show');
    
    Volt::route('properties/{property}/edit', 'properties.edit')
        ->middleware(['role:owner,manager'])
        ->name('properties.edit');

    // Unit Management Routes
    Volt::route('units/create', 'units.create')
        ->middleware(['role:owner,manager'])
        ->name('units.create');
    
    Volt::route('units/{unit}', 'units.show')
        ->middleware(['auth'])
        ->name('units.show');
    
    Volt::route('units/{unit}/edit', 'units.edit')
        ->middleware(['role:owner,manager'])
        ->name('units.edit');

    // Invoice Management Routes
    Volt::route('invoices', 'invoices.index')
        ->middleware(['auth'])
        ->name('invoices.index');
    
    Volt::route('invoices/create', 'invoices.create')
        ->middleware(['role:owner,manager'])
        ->name('invoices.create');
    
    Volt::route('invoices/{invoice}', 'invoices.show')
        ->middleware(['auth'])
        ->name('invoices.show');

    // Payment Management Routes
    Volt::route('payments', 'payments.index')
        ->middleware(['auth'])
        ->name('payments.index');
    
    Volt::route('payments/create', 'payments.create')
        ->middleware(['auth'])
        ->name('payments.create');
    
    Volt::route('payments/create/{invoice}', 'payments.create')
        ->middleware(['auth'])
        ->name('payments.create.invoice');

    // Tenant Management Routes
    Volt::route('tenants', 'tenants.index')
        ->middleware(['role:owner,manager'])
        ->name('tenants.index');
    
    Volt::route('tenants/create', 'tenants.create')
        ->middleware(['role:owner,manager'])
        ->name('tenants.create');
    
    Volt::route('tenants/create/{unit}', 'tenants.create')
        ->middleware(['role:owner,manager'])
        ->name('tenants.create.unit');
    
    Volt::route('tenants/{tenant}', 'tenants.show')
        ->middleware(['auth'])
        ->name('tenants.show');
    
    Volt::route('tenants/{tenant}/edit', 'tenants.edit')
        ->middleware(['role:owner,manager'])
        ->name('tenants.edit');

    // Reports Routes
    Volt::route('reports', 'reports.index')
        ->middleware(['role:owner,manager,admin'])
        ->name('reports.index');

    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    // Invoice PDF Routes
    Route::get('invoices/{invoice}/pdf', [\App\Http\Controllers\InvoicePdfController::class, 'download'])
        ->middleware(['auth'])
        ->name('invoices.pdf.download');
    
    Route::get('invoices/{invoice}/view-pdf', [\App\Http\Controllers\InvoicePdfController::class, 'view'])
        ->middleware(['auth'])
        ->name('invoices.pdf.view');

    // Payment Routes
    Route::get('invoices/{invoice}/pay', [\App\Http\Controllers\PaymentController::class, 'initiatePayment'])
        ->middleware(['auth'])
        ->name('payment.initiate');
    
    Route::get('payment/callback', [\App\Http\Controllers\PaymentController::class, 'handleCallback'])
        ->name('payment.callback');
    
    Route::post('payment/webhook', [\App\Http\Controllers\PaymentController::class, 'handleWebhook'])
        ->name('payment.webhook');
});
