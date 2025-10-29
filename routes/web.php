<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DevicesController;
use App\Http\Controllers\AdminUserController;

/*
|--------------------------------------------------------------------------
| Protected Pages (require login)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::view('/', 'pages.home')->name('dashboard');
    Route::view('/alerts', 'pages.alerts')->name('alerts');

    Route::get('/devices', [DevicesController::class, 'index'])->name('devices');
    Route::get('/devices/building/{building}', [DevicesController::class, 'byBuilding'])->name('devices.byBuilding');

    Route::view('/reports', 'pages.reports')->name('reports');
    Route::view('/settings', 'pages.settings')->name('settings');
    Route::view('/help', 'pages.help')->name('help');

    // Opcional: User Preview toggle
    Route::post('/enter-user-preview', function () {
        session()->put('user_preview', true);
        return back();
    })->name('enter.user.preview');

    Route::post('/exit-user-preview', function () {
        session()->forget('user_preview');
        return back();
    })->name('exit.user.preview');
});

/*
|--------------------------------------------------------------------------
| Admin-only (auth + admin middleware)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->group(function () {

    // 👉 Redirige /admin directamente a /admin/users
    Route::get('/admin', fn () => redirect()->route('admin.users.index'))->name('admin');

    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
    Route::patch('/admin/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('admin.users.updateRole');
    Route::delete('/admin/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
});

/*
|--------------------------------------------------------------------------
| Breeze Authentication Routes
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
