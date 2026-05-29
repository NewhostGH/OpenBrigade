<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Legacy\LegacyBridgeController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\ShortcutController;
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

    // Compatibility for middleware redirects generated from /index.php/* requests.
    Route::get('/index.php/login', [AuthController::class, 'showLogin'])->name('login.compat');
    Route::post('/index.php/login', [AuthController::class, 'login'])->name('login.attempt.compat');

    // Legacy scripts sometimes point to login.php explicitly.
    Route::get('/index.php/login.php', fn () => redirect('/login'));
    Route::get('/login.php', fn () => redirect('/login'));
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('personnel/{personnel}/photo', [PersonnelController::class, 'photo'])
        ->name('personnel.photo')
        ->middleware('permission:0');
    Route::resource('personnel', PersonnelController::class)
        ->only(['index', 'show', 'edit', 'update'])
        ->middleware('permission:0');
    Route::get('/legacy', fn () => redirect()->route('dashboard'))->name('dashboard.legacy');
    Route::get('/about', function () {
        return redirect('/legacy/about.php');
    })->name('about');
    Route::post('/shortcuts/toggle', [ShortcutController::class, 'toggle'])->name('shortcuts.toggle');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::match(['GET', 'POST'], '/index.php/logout', [AuthController::class, 'logout'])->name('logout.compat');

    Route::get('/{assetType}/{assetPath}', [LegacyBridgeController::class, 'asset'])
        ->where('assetType', 'css|js|images|webfonts')
        ->where('assetPath', '.*')
        ->name('legacy_bridge.asset');

    Route::get('/index.php/{assetType}/{assetPath}', [LegacyBridgeController::class, 'asset'])
        ->where('assetType', 'css|js|images|webfonts')
        ->where('assetPath', '.*')
        ->name('legacy_bridge.asset.compat.pathinfo');

    Route::get('/legacy/{assetType}/{assetPath}', [LegacyBridgeController::class, 'asset'])
        ->where('assetType', 'css|js|images|webfonts')
        ->where('assetPath', '.*')
        ->name('legacy_bridge.asset.legacy');

    // Legacy links frequently resolve as /index.php/{file}. Keep compatibility.
    Route::match(['GET', 'POST'], '/index.php/{legacyFile}', [LegacyBridgeController::class, 'show'])
        ->where('legacyFile', '.*')
        ->name('legacy_bridge.compat.pathinfo');
});

if (file_exists(__DIR__ . '/web_legacy_bridge.php')) {
    require __DIR__ . '/web_legacy_bridge.php';
}
