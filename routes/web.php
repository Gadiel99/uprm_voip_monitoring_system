<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| AUTH SIMULATION (Temporary Frontend Login)
|--------------------------------------------------------------------------
*/

// Show login page (if not logged in)
Route::get('/login', function () {
    if (Session::get('logged_in')) {
        return redirect('/');
    }
    return view('pages.login');
})->name('login');

// Handle login (accepts anything)
Route::post('/login', function (Request $request) {
    Session::put('logged_in', true);
    return redirect('/');
});

// Handle logout
Route::post('/logout', function () {
    Session::forget('logged_in');
    Session::forget('user_preview');
    return redirect('/login');
});

/*
|--------------------------------------------------------------------------
| USER PREVIEW TOGGLE
|--------------------------------------------------------------------------
*/
Route::post('/enter-user-preview', function () {
    Session::put('user_preview', true);
    return redirect('/')->with('activeTab', 'home');
})->name('enter.user.preview');

Route::post('/exit-user-preview', function () {
    Session::forget('user_preview');
    return back();
})->name('exit.user.preview');

/*
|--------------------------------------------------------------------------
| PROTECTED PAGES (only if logged in)
|--------------------------------------------------------------------------
*/
Route::group([], function () {

    // Small helper closure to guard routes
    $guard = function () {
        if (!Session::get('logged_in')) {
            return redirect('/login');
        }
    };

    Route::get('/', function () use ($guard) {
        if ($redirect = $guard()) return $redirect;
        return view('pages.home');
    });

    Route::get('/alerts', function () use ($guard) {
        if ($redirect = $guard()) return $redirect;
        return view('pages.alerts');
    });

    Route::get('/devices', function () use ($guard) {
        if ($redirect = $guard()) return $redirect;
        return view('pages.devices');
    });

    Route::get('/reports', function () use ($guard) {
        if ($redirect = $guard()) return $redirect;
        return view('pages.reports');
    });

    Route::get('/admin', function () use ($guard) {
        if ($redirect = $guard()) return $redirect;
        return view('pages.admin');
    });

    Route::get('/settings', function () use ($guard) {
        if ($redirect = $guard()) return $redirect;
        return view('pages.settings');
    });

    Route::get('/help', function () use ($guard) {
        if ($redirect = $guard()) return $redirect;
        return view('pages.help');
    });

});
