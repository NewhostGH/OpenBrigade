<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GeolocalisationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EvenementController;
use App\Http\Controllers\GardeController;
use App\Http\Controllers\DispoController;
use App\Http\Controllers\IndispoController;
use App\Http\Controllers\RemplacementController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ConsommableController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\StatistiqueController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\MaterielController;
use App\Http\Controllers\VehiculeController;
use App\Http\Controllers\Legacy\LegacyBridgeController;
use App\Http\Controllers\CotisationController;
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
    Route::get('/evenements', [EvenementController::class, 'index'])->name('evenement.index')->middleware('permission:0');
    Route::get('/evenements/create', [EvenementController::class, 'create'])->name('evenement.create')->middleware('permission:15');
    Route::post('/evenements', [EvenementController::class, 'store'])->name('evenement.store')->middleware('permission:15');
    Route::get('/evenements/{code}', [EvenementController::class, 'show'])->name('evenement.show')->middleware('permission:0');
    Route::get('/evenements/{code}/edit', [EvenementController::class, 'edit'])->name('evenement.edit')->middleware('permission:15');
    Route::put('/evenements/{code}', [EvenementController::class, 'update'])->name('evenement.update')->middleware('permission:15');
    Route::delete('/evenements/{code}', [EvenementController::class, 'destroy'])->name('evenement.destroy')->middleware('permission:19');
    // Participant management — inscription, fonction, équipe (permission 10 = inscrire)
    Route::post('/evenements/{code}/participants', [EvenementController::class, 'participantStore'])->name('evenement.participant.store')->middleware('permission:10');
    Route::patch('/evenements/{code}/participants/{pid}', [EvenementController::class, 'participantUpdate'])->name('evenement.participant.update')->middleware('permission:10');
    Route::patch('/evenements/{code}/participants/{pid}/equipe', [EvenementController::class, 'participantTeam'])->name('evenement.participant.team')->middleware('permission:10');
    Route::delete('/evenements/{code}/participants/{pid}', [EvenementController::class, 'participantDestroy'])->name('evenement.participant.destroy')->middleware('permission:10');
    // Équipes CRUD — teams within an event (permission 15 = gérer activité)
    Route::post('/evenements/{code}/equipes', [EvenementController::class, 'equipeStore'])->name('evenement.equipe.store')->middleware('permission:15');
    Route::put('/evenements/{code}/equipes/{ee}', [EvenementController::class, 'equipeUpdate'])->name('evenement.equipe.update')->middleware('permission:15');
    Route::delete('/evenements/{code}/equipes/{ee}', [EvenementController::class, 'equipeDestroy'])->name('evenement.equipe.destroy')->middleware('permission:15');
    Route::post('/evenements/{code}/equipes/{ee}/participants', [EvenementController::class, 'equipeAddParticipant'])->name('evenement.equipe.participant.add')->middleware('permission:10');
    Route::post('/evenements/{code}/equipes/{ee}/materiels', [EvenementController::class, 'equipeAddMateriel'])->name('evenement.equipe.materiel.add')->middleware('permission:15');
    // Renforts — attach/detach reinforcement sub-events (permission 15)
    Route::post('/evenements/{code}/renforts', [EvenementController::class, 'renfortAttach'])->name('evenement.renfort.attach')->middleware('permission:15');
    Route::delete('/evenements/{code}/renforts/{renfort}', [EvenementController::class, 'renfortDetach'])->name('evenement.renfort.detach')->middleware('permission:15');
    // Véhicules — attach/detach vehicles (permission 15)
    Route::post('/evenements/{code}/vehicules', [EvenementController::class, 'vehiculeAttach'])->name('evenement.vehicule.attach')->middleware('permission:15');
    Route::delete('/evenements/{code}/vehicules/{vehicule}', [EvenementController::class, 'vehiculeDetach'])->name('evenement.vehicule.detach')->middleware('permission:15');
    // Matériel — assign/update-qty/detach equipment (permission 15)
    Route::post('/evenements/{code}/materiels', [EvenementController::class, 'materielAttach'])->name('evenement.materiel.attach')->middleware('permission:15');
    Route::patch('/evenements/{code}/materiels/{ma}', [EvenementController::class, 'materielUpdateQty'])->name('evenement.materiel.qty')->middleware('permission:15');
    Route::delete('/evenements/{code}/materiels/{ma}', [EvenementController::class, 'materielDetach'])->name('evenement.materiel.detach')->middleware('permission:15');
    // Exports
    Route::get('/evenements/{code}/export/participants', [EvenementController::class, 'exportParticipants'])->name('evenement.export.participants')->middleware('permission:0');
    Route::get('/evenements/{code}/ical', [EvenementController::class, 'exportIcal'])->name('evenement.ical')->middleware('permission:0');
    Route::get('/garde', [GardeController::class, 'index'])->name('garde.index')->middleware('permission:61');
    Route::get('/garde/astreintes', [GardeController::class, 'astreintes'])->name('garde.astreintes')->middleware('permission:52');
    Route::get('/indisponibilites', [IndispoController::class, 'index'])->name('indispo.index')->middleware('permission:11');
    Route::get('/remplacements', [RemplacementController::class, 'index'])->name('remplacement.index')->middleware('permission:0');
    Route::get('/disponibilites', [DispoController::class, 'index'])->name('dispo.index')->middleware('permission:38');
    Route::get('/admin/monitoring', [AdminController::class, 'monitoring'])->name('admin.monitoring')->middleware('permission:49');
    Route::get('/cotisations', [CotisationController::class, 'index'])->name('cotisations.index')->middleware('permission:53');
    Route::post('/cotisations', [CotisationController::class, 'batchSave'])->name('cotisations.save')->middleware('permission:53');
    Route::get('/cotisations/export', [CotisationController::class, 'export'])->name('cotisations.export')->middleware('permission:53');
    Route::get('/cotisations/prelevements', [CotisationController::class, 'prelevements'])->name('cotisations.prelevements')->middleware('permission:53');
    Route::post('/cotisations/prelevements', [CotisationController::class, 'savePrelevements'])->name('cotisations.prelevements.save')->middleware('permission:53');
    Route::get('/cotisations/virements', [CotisationController::class, 'virements'])->name('cotisations.virements')->middleware('permission:53');
    Route::get('/planning', [PlanningController::class, 'index'])->name('planning.index')->middleware('permission:0');
    Route::get('/vehicules', [VehiculeController::class, 'index'])->name('vehicule.index')->middleware('permission:42');
    Route::get('/vehicules/create', [VehiculeController::class, 'create'])->name('vehicule.create')->middleware('permission:17');
    Route::post('/vehicules', [VehiculeController::class, 'store'])->name('vehicule.store')->middleware('permission:17');
    Route::get('/vehicules/{vehicule}', [VehiculeController::class, 'show'])->name('vehicule.show')->middleware('permission:42');
    Route::get('/vehicules/{vehicule}/edit', [VehiculeController::class, 'edit'])->name('vehicule.edit')->middleware('permission:17');
    Route::put('/vehicules/{vehicule}', [VehiculeController::class, 'update'])->name('vehicule.update')->middleware('permission:17');
    Route::delete('/vehicules/{vehicule}', [VehiculeController::class, 'destroy'])->name('vehicule.destroy')->middleware('permission:19');
    Route::get('/materiels', [MaterielController::class, 'index'])->name('materiel.index')->middleware('permission:42');
    Route::get('/consommables', [ConsommableController::class, 'index'])->name('consommable.index')->middleware('permission:42');
    Route::get('/documents', [DocumentController::class, 'index'])->name('document.index')->middleware('permission:44');
    Route::get('/messages', [MessageController::class, 'index'])->name('message.index')->middleware('permission:44');
    Route::get('/organisation', [OrganisationController::class, 'index'])->name('organisation.index')->middleware('permission:52');
    Route::get('/statistiques', [StatistiqueController::class, 'index'])->name('statistique.index')->middleware('permission:27');
    Route::get('personnel/{personnel}/photo', [PersonnelController::class, 'photo'])
        ->name('personnel.photo')
        ->middleware('permission:0');
    Route::get('personnel/grade/{grade}', [PersonnelController::class, 'gradeImage'])
        ->name('personnel.grade_image')
        ->where('grade', '[A-Z0-9]+')
        ->middleware('permission:0');
    // Personnel list exports (static segments before resource wildcard)
    Route::get('personnel/export/xls', [PersonnelController::class, 'exportXls'])
        ->name('personnel.export.xls')->middleware('permission:0');
    Route::get('personnel/export/csv', [PersonnelController::class, 'exportCsv'])
        ->name('personnel.export.csv')->middleware('permission:0');
    Route::get('personnel/create', [PersonnelController::class, 'create'])
        ->name('personnel.create')->middleware('permission:1');
    Route::post('personnel', [PersonnelController::class, 'store'])
        ->name('personnel.store')->middleware('permission:1');
    Route::resource('personnel', PersonnelController::class)
        ->only(['index', 'show', 'edit', 'update'])
        ->middleware('permission:0');
    // Qualifications (competences) CRUD — nested under personnel
    Route::post('personnel/{personnel}/qualifications', [PersonnelController::class, 'storeQualification'])
        ->name('personnel.qualification.store')->middleware('permission:0');
    Route::patch('personnel/{personnel}/qualifications/{psId}', [PersonnelController::class, 'updateQualification'])
        ->name('personnel.qualification.update')->middleware('permission:0');
    Route::delete('personnel/{personnel}/qualifications/{psId}', [PersonnelController::class, 'destroyQualification'])
        ->name('personnel.qualification.destroy')->middleware('permission:0');
    // Cotisations CRUD — nested under personnel
    Route::post('personnel/{personnel}/cotisations', [PersonnelController::class, 'storeCotisation'])
        ->name('personnel.cotisation.store')->middleware('permission:0');
    Route::patch('personnel/{personnel}/cotisations/{pcId}', [PersonnelController::class, 'updateCotisation'])
        ->name('personnel.cotisation.update')->middleware('permission:0');
    Route::delete('personnel/{personnel}/cotisations/{pcId}', [PersonnelController::class, 'destroyCotisation'])
        ->name('personnel.cotisation.destroy')->middleware('permission:0');
    // Per-member exports
    Route::get('personnel/{personnel}/vcard', [PersonnelController::class, 'exportVcard'])
        ->name('personnel.vcard')->middleware('permission:0');
    Route::get('personnel/{personnel}/livret', [PersonnelController::class, 'exportLivret'])
        ->name('personnel.livret')->middleware('permission:0');
    Route::get('personnel/{personnel}/carte', [PersonnelController::class, 'exportCarte'])
        ->name('personnel.carte')->middleware('permission:0');
    // Géolocalisation
    Route::get('/geolocalisation', [GeolocalisationController::class, 'index'])
        ->name('geolocalisation.index')->middleware('permission:0');
    Route::post('personnel/{personnel}/gps', [GeolocalisationController::class, 'updateGps'])
        ->name('personnel.gps.update')->middleware('permission:0');
    Route::get('/trombinoscope', [PersonnelController::class, 'trombinoscope'])->name('personnel.trombinoscope')->middleware('permission:0');
    Route::get('/qualifications', [PersonnelController::class, 'qualifications'])->name('personnel.qualifications')->middleware('permission:56');
    Route::get('/clients', [CompanyController::class, 'index'])->name('company.index')->middleware('permission:29');
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
