<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DatabaseBackupController;
use App\Http\Controllers\Admin\ImageController;
use App\Http\Controllers\Admin\ImportBatchController;
use App\Http\Controllers\Admin\ReferenceDataController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\FirstAdminSetupController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WeedSpeciesController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [SessionController::class, 'create'])->name('login');
    Route::post('/login', [SessionController::class, 'store'])->middleware('throttle:login')->name('login.store');

    Route::get('/setup', [FirstAdminSetupController::class, 'create'])->name('setup.create');
    Route::post('/setup', [FirstAdminSetupController::class, 'store'])->middleware('throttle:5,1')->name('setup.store');
});

Route::view('/developers', 'developers')->name('developers.index');

Route::post('/logout', [SessionController::class, 'destroy'])
    ->middleware(['auth', 'active'])
    ->name('logout');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('species', WeedSpeciesController::class)
        ->only(['create', 'store', 'edit', 'update', 'destroy'])
        ->parameters(['species' => 'weedSpecies'])
        ->middleware('role:Administrator');

    Route::get('/species', [WeedSpeciesController::class, 'index'])->name('species.index');
    Route::get('/species/{weedSpecies}', [WeedSpeciesController::class, 'show'])->name('species.show');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::middleware('role:Administrator')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('/images', [ImageController::class, 'index'])->name('images.index');
        Route::delete('/images/{image}', [ImageController::class, 'destroy'])->name('images.destroy');

        Route::get('/references/{type}', [ReferenceDataController::class, 'index'])->name('references.index');
        Route::post('/references/{type}', [ReferenceDataController::class, 'store'])->name('references.store');
        Route::put('/references/{type}/{reference}', [ReferenceDataController::class, 'update'])->name('references.update');
        Route::delete('/references/{type}/{reference}', [ReferenceDataController::class, 'destroy'])->name('references.destroy');

        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

        Route::get('/imports', [ImportBatchController::class, 'index'])->name('imports.index');
        Route::post('/imports', [ImportBatchController::class, 'store'])->middleware('throttle:2,1')->name('imports.store');

        Route::get('/backups', [DatabaseBackupController::class, 'index'])->name('backups.index');
        Route::post('/backups', [DatabaseBackupController::class, 'store'])->middleware('throttle:2,1')->name('backups.store');
        Route::get('/backups/{databaseBackup}/download', [DatabaseBackupController::class, 'download'])->name('backups.download');
        Route::post('/backups/{databaseBackup}/restore', [DatabaseBackupController::class, 'restore'])->middleware('throttle:2,1')->name('backups.restore');
    });
});
