<?php

use Illuminate\Support\Facades\Route;

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