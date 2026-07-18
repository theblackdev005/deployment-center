<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeploymentController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\HostingerAccountController;
use App\Http\Controllers\HostingerInventoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\SetupController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/configuration', SetupController::class)->name('setup.index');
    Route::get('/hostinger', HostingerInventoryController::class)->name('hostinger.index');
    Route::get('/hostinger/accounts', [HostingerAccountController::class, 'index'])->name('hostinger.accounts.index');
    Route::post('/hostinger/accounts', [HostingerAccountController::class, 'store'])->name('hostinger.accounts.store');
    Route::patch('/hostinger/accounts/{hostingerAccount}', [HostingerAccountController::class, 'update'])->name('hostinger.accounts.update');
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

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
