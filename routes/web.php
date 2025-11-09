<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DevicesController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ProfileController; // <-- add
use App\Http\Controllers\ReportsController;

/**
 * Controllers overview:
 *
 * - DevicesController:
 *     GET /devices                -> index(): Show devices overview with buildings table.
 *     GET /devices/building/{b}   -> byBuilding(): Filter devices by building slug/name.
 *
 * - AdminUserController (admin-only):
 *     GET    /admin/users                 -> index(): Render Admin page with Users tab (DB-backed).
 *     POST   /admin/users                 -> store(): Create a new user (name, email, password, role).
 *     PATCH  /admin/users/{user}/role     -> updateRole(): Change role (user|admin). Self and super_admin safe-guards.
 *     DELETE /admin/users/{user}          -> destroy(): Delete user (blocked for self and super_admin).
 *     (Optional mock endpoints reserved for Settings/Critical Devices if later wired)
 *
 * - ProfileController (authenticated):
 *     GET    /profile                     -> edit(): Profile settings page (tabs for username/email/password).
 *     PATCH  /profile                     -> update(): Update name/email (with validation).
 *     PATCH  /profile/password            -> updatePassword(): Update password (current + confirmation).
 *     DELETE /profile                     -> destroy(): Delete current user after password confirmation.
 *
 * - AccountController:
 *     Reserved for future account UX flows (not wired in routes currently).
 */

/*
|--------------------------------------------------------------------------
| Protected Pages (require login)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Dashboard and tabs (server-rendered pages)
    Route::view('/', 'pages.home')->name('dashboard');
    Route::view('/alerts', 'pages.alerts')->name('alerts');

    // Devices: building-level browsing
    Route::get('/devices', [DevicesController::class, 'index'])->name('devices');
    Route::get('/devices/critical', [DevicesController::class, 'criticalDevices'])->name('devices.critical');
    Route::get('/devices/building/{building}', [DevicesController::class, 'byBuilding'])->name('devices.byBuilding');

    // Reports: search and filtering
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports');
    Route::get('/reports/search', [ReportsController::class, 'search'])->name('reports.search');

    // Deprecated: standalone Settings page (Admin->Settings mock-up remains within Admin)
    // Route::view('/settings', 'pages.settings')->name('settings');

    Route::view('/help', 'pages.help')->name('help');

    // User Preview (admins only): toggles limited UI without leaving session
    Route::post('/enter-user-preview', function () {
        session()->put('user_preview', true);
        return back();
    })->middleware('admin')->name('enter.user.preview');

    Route::post('/exit-user-preview', function () {
        session()->forget('user_preview');
        return back();
    })->middleware('admin')->name('exit.user.preview');

    // Profile management routes (Breeze-like)
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
| Renders Admin page with mock-up tabs (Backup/Logs/Settings/Servers) and
| a server-driven Users tab backed by AdminUserController.
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','admin'])->group(function () {
    // Admin dashboard
    Route::get('/admin', [AdminController::class, 'index'])->name('admin');

    // Users tab (DB-backed)
    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users');
    Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
    Route::patch('/admin/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::patch('/admin/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('admin.users.role');
    Route::delete('/admin/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
});

/*
|--------------------------------------------------------------------------
| Breeze Authentication Routes
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
