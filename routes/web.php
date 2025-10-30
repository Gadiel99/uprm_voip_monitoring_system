<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DevicesController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ProfileController; // <-- add

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

    // Opcional: User Preview toggle (solo admins/superadmins)
    Route::post('/enter-user-preview', function () {
        session()->put('user_preview', true);
        return back();
    })->middleware('admin')->name('enter.user.preview');

    Route::post('/exit-user-preview', function () {
        session()->forget('user_preview');
        return back();
    })->middleware('admin')->name('exit.user.preview');

    // Profile routes (unificar edición en PATCH /profile)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Legacy compatibility to avoid 404s and old bookmarks/forms
    Route::get('/profile/username', fn () => redirect()->route('profile.edit'));
    Route::get('/profile/email', fn () => redirect()->route('profile.edit'));
    Route::patch('/profile/username', [ProfileController::class, 'updateUsername']);
    Route::patch('/profile/email', [ProfileController::class, 'updateEmail']);
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
