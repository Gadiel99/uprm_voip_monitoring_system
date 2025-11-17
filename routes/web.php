<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DevicesController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ProfileController; // <-- add
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\AlertsController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DeviceActivityController;

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
    Route::get('/', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/alerts', [AlertsController::class, 'index'])->name('alerts');
    Route::get('/alerts/building/{building}/offline', [AlertsController::class, 'offlineDevices'])->name('alerts.offlineDevices');
    Route::get('/alerts/critical/offline', [AlertsController::class, 'criticalOffline'])->name('alerts.criticalOffline');

    // Devices: building-level browsing
    Route::get('/devices', [DevicesController::class, 'index'])->name('devices');
    Route::get('/devices/critical', [DevicesController::class, 'criticalDevices'])->name('devices.critical');
    Route::get('/devices/unmapped', [DevicesController::class, 'unmapped'])->name('devices.unmapped');
    Route::get('/devices/unmapped/network/{network}', [DevicesController::class, 'unmappedNetwork'])->name('devices.unmappedNetwork');
    Route::get('/devices/building/{building}', [DevicesController::class, 'byBuilding'])->name('devices.byBuilding');
    Route::get('/devices/building/{building}/network/{network}', [DevicesController::class, 'byNetwork'])->name('devices.byNetwork');

    // Reports: search and filtering
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports');
    Route::get('/reports/search', [ReportsController::class, 'search'])->name('reports.search');

    // Deprecated: standalone Settings page (Admin->Settings mock-up remains within Admin)
    // Route::view('/settings', 'pages.settings')->name('settings');

    Route::view('/help', 'pages.help')->name('help');

    // Profile management routes (Breeze-like)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Legacy compatibility to avoid 404s and old bookmarks/forms
    Route::get('/profile/username', fn () => redirect()->route('profile.edit'));
    Route::get('/profile/email', fn () => redirect()->route('profile.edit'));
    Route::patch('/profile/username', [ProfileController::class, 'updateUsername'])->name('profile.username');
    Route::patch('/profile/email', [ProfileController::class, 'updateEmail'])->name('profile.email');
    
    // Building management (for campus map)
    Route::get('/buildings', [BuildingController::class, 'index'])->name('buildings.index');
    Route::get('/networks/unassigned', [BuildingController::class, 'getUnassignedNetworks'])->name('networks.unassigned');
    
    // Admin-only building operations
    Route::middleware('admin')->group(function () {
        Route::post('/buildings', [BuildingController::class, 'store'])->name('buildings.store');
        Route::put('/buildings/{id}', [BuildingController::class, 'update'])->name('buildings.update');
        Route::delete('/buildings/{id}', [BuildingController::class, 'destroy'])->name('buildings.destroy');
    });
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
    
    // Alert settings (Settings tab)
    Route::post('/admin/alert-settings', [AdminController::class, 'updateAlertSettings'])->name('admin.alert-settings.update');
    
    // Critical devices management (Settings tab)
    Route::post('/admin/critical-devices', [AdminController::class, 'storeCriticalDevice'])->name('admin.critical-devices.store');
    Route::delete('/admin/critical-devices/{device}', [AdminController::class, 'destroyCriticalDevice'])->name('admin.critical-devices.destroy');
    
    // API: Get critical devices status (for notifications)
    Route::get('/api/critical-devices/status', [AdminController::class, 'getCriticalDevicesStatus'])->name('api.critical-devices.status');
    
    // Notification preferences
    Route::post('/admin/notification-preferences', [AdminController::class, 'updateNotificationPreferences'])->name('admin.notification-preferences.update');
    
    // Device Activity API
    Route::get('/api/device-activity/{deviceId}', [DeviceActivityController::class, 'getActivity'])->name('api.device-activity');
    Route::get('/api/device-activity/{deviceId}/both', [DeviceActivityController::class, 'getBothDays'])->name('api.device-activity.both');
});

/*
|--------------------------------------------------------------------------
| Breeze Authentication Routes
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
