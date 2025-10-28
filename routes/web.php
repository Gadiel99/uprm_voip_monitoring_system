<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Protected Pages (require login)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::view('/', 'pages.home')->name('dashboard');
    Route::view('/alerts', 'pages.alerts')->name('alerts');
    Route::view('/devices', 'pages.devices')->name('devices');
    Route::view('/reports', 'pages.reports')->name('reports');
    Route::view('/admin', 'pages.admin')->name('admin');
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
| Breeze Authentication Routes
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
