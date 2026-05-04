<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Routes are migrated here from the legacy flat-file layout incrementally.
| Each domain area (auth, personnel, events, …) gets its own route group
| once the corresponding controller and Blade views exist.
|
*/

Route::get('/', function () {
    // TODO: replace with DashboardController once migrated
    return redirect()->route('login');
})->name('home');

Route::get('/login', function () {
    // TODO: replace with AuthController::showLogin
    return view('auth.login');
})->name('login');
