<?php

use App\Http\Controllers\ClientSiteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeploymentController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\HostingerAccountController;
use App\Http\Controllers\HostingerInventoryController;
use App\Http\Controllers\InstallationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PwaManifestController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\TwoFactorSettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/installation', [InstallationController::class, 'show'])->name('installation.show');
Route::post('/installation/check', [InstallationController::class, 'check'])->name('installation.check');
Route::post('/installation', [InstallationController::class, 'store'])->name('installation.store');
Route::get('/manifest.webmanifest', PwaManifestController::class)->name('pwa.manifest');

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware(['auth', 'verified', 'two-factor.configured'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/configuration', SetupController::class)->name('setup.index');
    Route::get('/hostinger', HostingerInventoryController::class)->name('hostinger.index');
    Route::get('/clients-sites', [ClientSiteController::class, 'index'])->name('clients-sites.index');
    Route::post('/clients', [ClientSiteController::class, 'storeCustomer'])->name('clients.store');
    Route::patch('/clients/{customer}', [ClientSiteController::class, 'updateCustomer'])->name('clients.update');
    Route::delete('/clients/{customer}', [ClientSiteController::class, 'destroyCustomer'])->name('clients.destroy');
    Route::post('/managed-sites', [ClientSiteController::class, 'storeSite'])->name('managed-sites.store');
    Route::patch('/managed-sites/{managedSite}', [ClientSiteController::class, 'updateSite'])->name('managed-sites.update');
    Route::delete('/managed-sites/{managedSite}', [ClientSiteController::class, 'destroySite'])->name('managed-sites.destroy');
    Route::get('/hostinger/accounts', [HostingerAccountController::class, 'index'])->name('hostinger.accounts.index');
    Route::post('/hostinger/accounts', [HostingerAccountController::class, 'store'])->name('hostinger.accounts.store');
    Route::patch('/hostinger/accounts/{hostingerAccount}', [HostingerAccountController::class, 'update'])->name('hostinger.accounts.update');
    Route::patch('/hostinger/accounts/{hostingerAccount}/status', [HostingerAccountController::class, 'updateStatus'])->name('hostinger.accounts.status');
    Route::post('/hostinger/accounts/sync-all', [HostingerAccountController::class, 'syncAll'])->name('hostinger.accounts.sync-all');
    Route::post('/hostinger/accounts/{hostingerAccount}/sync', [HostingerAccountController::class, 'sync'])->name('hostinger.accounts.sync');
    Route::delete('/hostinger/accounts/{hostingerAccount}', [HostingerAccountController::class, 'destroy'])->name('hostinger.accounts.destroy');
    Route::resource('projects', ProjectController::class)->only(['index', 'store', 'destroy']);
    Route::resource('servers', ServerController::class)->only(['index', 'store', 'destroy']);
    Route::resource('domains', DomainController::class)->only(['index', 'store', 'destroy']);
    Route::get('/deployments', [DeploymentController::class, 'index'])->name('deployments.index');
    Route::get('/deployments/create', [DeploymentController::class, 'create'])->name('deployments.create');
    Route::post('/deployments', [DeploymentController::class, 'store'])->name('deployments.store');
    Route::post('/deployments/{deployment}/retry', [DeploymentController::class, 'retry'])->name('deployments.retry');
    Route::get('/deployments/{deployment}', [DeploymentController::class, 'show'])->name('deployments.show');

});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/profile/two-factor', [TwoFactorSettingsController::class, 'enable'])
        ->name('two-factor.enable');
    Route::post('/profile/two-factor/confirm', [TwoFactorSettingsController::class, 'confirm'])
        ->name('two-factor.confirm');
    Route::post('/profile/two-factor/disable', [TwoFactorSettingsController::class, 'disable'])
        ->name('two-factor.disable');
    Route::post('/profile/two-factor/recovery-codes', [TwoFactorSettingsController::class, 'regenerateRecoveryCodes'])
        ->name('two-factor.recovery-codes');
});

require __DIR__.'/auth.php';
