<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

// ─── Page Routes ────────────────────────────────────────────────
Route::get('/', function () {
    return view('pages.home');
});

Route::get('/alerts', function () {
    return view('pages.alerts');
});

Route::get('/devices', function () {
    return view('pages.devices');
});

Route::get('/reports', function () {
    return view('pages.reports');
});

Route::get('/admin', function () {
    return view('pages.admin');
});

Route::get('/settings', function () {
    return view('pages.settings');
});

Route::get('/help', function () {
    return view('pages.help');
});


// ─── User Preview Mode (Session Toggle) ─────────────────────────
Route::post('/enter-user-preview', function () {
    Session::put('user_preview', true);
    return back();
})->name('enter.user.preview');

Route::post('/exit-user-preview', function () {
    Session::forget('user_preview');
    return back();
})->name('exit.user.preview');
