<?php

use App\Http\Controllers\Api\V1\ImportExportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Versioned webservices, documented in docs/admin/api.md. Every route here
| is prefixed with /api by the framework.
|
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->name('api.v1.')->middleware('throttle:api')->group(function () {
    Route::post('personnel/search', [ImportExportController::class, 'searchPersonnel'])
        ->middleware('api.token:export')->name('personnel.search');

    Route::middleware('api.token:import')->group(function () {
        Route::post('personnel/import', [ImportExportController::class, 'importPersonnel'])->name('personnel.import');
        Route::post('events/import', [ImportExportController::class, 'importEvent'])->name('events.import');
    });
});

// Legacy eBrigade paths, kept so existing clients work unchanged after the
// upgrade (same JSON in, same JSON out). New clients use /api/v1.
// TODO: Migrate code (drop once every consumer calls /api/v1)
Route::middleware('throttle:api')->group(function () {
    Route::post('export/search.php', [ImportExportController::class, 'searchPersonnel'])->middleware('api.token:export');
    Route::post('import/people.php', [ImportExportController::class, 'importPersonnel'])->middleware('api.token:import');
    Route::post('import/event.php', [ImportExportController::class, 'importEvent'])->middleware('api.token:import');
});
