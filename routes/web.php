<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PersonnelController;
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
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('personnel/{personnel}/photo', [PersonnelController::class, 'photo'])
        ->name('personnel.photo')
        ->middleware('permission:0');
    Route::resource('personnel', PersonnelController::class)
        ->only(['index', 'show', 'edit', 'update'])
        ->middleware('permission:0');
    Route::get('/dashboard/legacy', function () {
        return redirect()->route('legacy_migrated.index');
    })->name('dashboard.legacy');
    Route::get('/about', function () {
        return redirect('/legacy-migrated/about');
    })->name('about');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

if (file_exists(__DIR__ . '/web_legacy_migrated.php')) {
    require __DIR__ . '/web_legacy_migrated.php';
}
