<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PermissionMatrixController;
use App\Http\Controllers\Admin\PermissionsController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\UsersController;

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::middleware('permission:manage users')->group(function () {
            Route::get('/users', [UsersController::class, 'index'])->name('users.index');
            Route::get('/users/create', [UsersController::class, 'create'])->name('users.create');
            Route::post('/users', [UsersController::class, 'store'])->name('users.store');
            Route::get('/users/{user}/edit', [UsersController::class, 'edit'])->name('users.edit');
            Route::put('/users/{user}', [UsersController::class, 'update'])->name('users.update');
            Route::delete('/users/{user}', [UsersController::class, 'destroy'])->name('users.destroy');
        });

        Route::middleware('permission:manage roles')->group(function () {
            Route::get('/roles', [RolesController::class, 'index'])->name('roles.index');
            Route::post('/roles', [RolesController::class, 'store'])->name('roles.store');
            Route::get('/roles/{role}', [RolesController::class, 'show'])->name('roles.show');
            Route::put('/roles/{role}', [RolesController::class, 'update'])->name('roles.update');
            Route::delete('/roles/{role}', [RolesController::class, 'destroy'])->name('roles.destroy');
            Route::patch('/roles/{role}/permissions', [RolesController::class, 'updatePermissions'])->name('roles.permissions.update');

            Route::get('/permissions', [PermissionsController::class, 'index'])->name('permissions.index');
            Route::post('/permissions', [PermissionsController::class, 'store'])->name('permissions.store');
            Route::put('/permissions/{permission}', [PermissionsController::class, 'update'])->name('permissions.update');
            Route::delete('/permissions/{permission}', [PermissionsController::class, 'destroy'])->name('permissions.destroy');

            Route::get('/matrix', [PermissionMatrixController::class, 'index'])->name('matrix.index');
            Route::post('/matrix', [PermissionMatrixController::class, 'update'])->name('matrix.update');
        });
    });

Route::middleware(['auth', 'role:agent'])
    ->prefix('agent')
    ->name('agent.')
    ->group(function () {
        Route::view('/', 'agent.dashboard')->name('dashboard');
    });

require __DIR__.'/auth.php';
