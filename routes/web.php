<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ConsommableController;
use App\Http\Controllers\ContextController;
use App\Http\Controllers\CotisationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DispoController;
use App\Http\Controllers\DocumentAclController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\EvenementController;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\GardeController;
use App\Http\Controllers\GeolocalisationController;
use App\Http\Controllers\HabilitationController;
use App\Http\Controllers\IndispoController;
use App\Http\Controllers\Legacy\LegacyBridgeController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\MaterielController;
use App\Http\Controllers\MesDroitsController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\ParametrageController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\PluginController;
use App\Http\Controllers\RemplacementController;
use App\Http\Controllers\ShortcutController;
use App\Http\Controllers\StatistiqueController;
use App\Http\Controllers\TotpController;
use App\Http\Controllers\VehiculeController;
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

    // TOTP challenge — shown after correct password when 2FA is enabled.
    // Intentionally inside 'guest' so already-authenticated users are bounced.
    Route::get('/totp/challenge', [TotpController::class, 'showChallenge'])->name('totp.challenge');
    Route::post('/totp/challenge', [TotpController::class, 'verifyChallenge'])->name('totp.challenge.verify');

    // Self-service password reset (no auth required)
    Route::get('/password/reset', [PasswordResetController::class, 'showRequestForm'])->name('password.request');
    Route::post('/password/reset', [PasswordResetController::class, 'sendResetToken'])->name('password.email');
    Route::get('/password/reset/{token}', [PasswordResetController::class, 'confirmToken'])->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/layout', [DashboardController::class, 'saveLayout'])->name('dashboard.layout.save');
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
    Route::get('/remplacements', [RemplacementController::class, 'index'])->name('remplacement.index')->middleware(['permission:0', 'feature:remplacements']);
    Route::get('/disponibilites', [DispoController::class, 'index'])->name('dispo.index')->middleware('permission:38');
    Route::get('/admin/monitoring', [AdminController::class, 'monitoring'])->name('admin.monitoring')->middleware('permission:49');
    // Backup & restore
    Route::get('/admin/sauvegarde', [BackupController::class, 'index'])->name('admin.backup')->middleware('permission:14');
    Route::post('/admin/sauvegarde', [BackupController::class, 'store'])->name('admin.backup.store')->middleware('permission:14');
    Route::get('/admin/sauvegarde/{filename}/download', [BackupController::class, 'download'])->name('admin.backup.download')->middleware('permission:14');
    Route::delete('/admin/sauvegarde/{filename}', [BackupController::class, 'destroy'])->name('admin.backup.destroy')->middleware('permission:14');
    Route::post('/admin/sauvegarde/restore', [BackupController::class, 'restore'])->name('admin.backup.restore')->middleware('permission:14');
    Route::patch('/admin/sauvegarde/parametres', [BackupController::class, 'updateSettings'])->name('admin.backup.settings')->middleware('permission:14');
    // Maintenance (upgrade.php superseded by artisan migrate)
    Route::get('/admin/maintenance', [MaintenanceController::class, 'index'])->name('admin.maintenance')->middleware('permission:14');
    Route::get('/admin/security', [AdminController::class, 'security'])->name('admin.security')->middleware('permission:14');
    Route::get('/admin/security/politique/create', [AdminController::class, 'policyCreate'])->name('admin.policy.create')->middleware('permission:14');
    Route::post('/admin/security/politique', [AdminController::class, 'policyStore'])->name('admin.policy.store')->middleware('permission:14');
    Route::get('/admin/security/politique/{id}/edit', [AdminController::class, 'policyEdit'])->name('admin.policy.edit')->middleware('permission:14');
    Route::patch('/admin/security/politique/{id}', [AdminController::class, 'policyUpdate'])->name('admin.policy.update')->middleware('permission:14');
    Route::delete('/admin/security/politique/{id}', [AdminController::class, 'policyDestroy'])->name('admin.policy.destroy')->middleware('permission:14');
    Route::post('/admin/security/ldap-test', [AdminController::class, 'testLdap'])->name('admin.ldap.test')->middleware('permission:14');
    Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings')->middleware('permission:14');
    Route::patch('/admin/settings/{id}', [AdminController::class, 'saveSetting'])->name('admin.settings.save')->middleware('permission:14');
    Route::post('/admin/settings/{id}/upload', [AdminController::class, 'uploadSetting'])->name('admin.settings.upload')->middleware('permission:14');
    Route::delete('/admin/settings/{id}/file', [AdminController::class, 'deleteSetting'])->name('admin.settings.delete-file')->middleware('permission:14');

    // ── Fonctionnalités & Modules — unified feature registry (ob_feature) ──────
    Route::get('/admin/fonctionnalites', [FeatureController::class, 'index'])->name('admin.fonctionnalites')->middleware('permission:14');
    Route::patch('/admin/fonctionnalites/{feature}', [FeatureController::class, 'toggle'])->name('admin.fonctionnalites.toggle')->middleware('permission:14');

    // ── Plugins — community plugin marketplace (WIP placeholder) ──────────────
    Route::get('/admin/plugins', [PluginController::class, 'index'])->name('admin.plugins')->middleware('permission:14');

    // ── Paramétrage — reference table CRUD ────────────────────────────────────
    Route::get('/admin/parametrage', [ParametrageController::class, 'index'])->name('admin.parametrage')->middleware('permission:5');
    // Type événement
    Route::get('/admin/parametrage/type-evenement', [ParametrageController::class, 'typeEvenementIndex'])->name('admin.parametrage.type-evenement')->middleware('permission:5');
    Route::post('/admin/parametrage/type-evenement', [ParametrageController::class, 'typeEvenementStore'])->name('admin.parametrage.type-evenement.store')->middleware('permission:5');
    Route::patch('/admin/parametrage/type-evenement/{code}', [ParametrageController::class, 'typeEvenementUpdate'])->name('admin.parametrage.type-evenement.update')->middleware('permission:5');
    Route::delete('/admin/parametrage/type-evenement/{code}', [ParametrageController::class, 'typeEvenementDestroy'])->name('admin.parametrage.type-evenement.destroy')->middleware('permission:5');
    // Type participation
    Route::get('/admin/parametrage/type-participation', [ParametrageController::class, 'typeParticipationIndex'])->name('admin.parametrage.type-participation')->middleware('permission:5');
    Route::post('/admin/parametrage/type-participation', [ParametrageController::class, 'typeParticipationStore'])->name('admin.parametrage.type-participation.store')->middleware('permission:5');
    Route::patch('/admin/parametrage/type-participation/{id}', [ParametrageController::class, 'typeParticipationUpdate'])->name('admin.parametrage.type-participation.update')->middleware('permission:5');
    Route::delete('/admin/parametrage/type-participation/{id}', [ParametrageController::class, 'typeParticipationDestroy'])->name('admin.parametrage.type-participation.destroy')->middleware('permission:5');
    // Type matériel
    Route::get('/admin/parametrage/type-materiel', [ParametrageController::class, 'typeMaterielIndex'])->name('admin.parametrage.type-materiel')->middleware('permission:5');
    Route::post('/admin/parametrage/type-materiel', [ParametrageController::class, 'typeMaterielStore'])->name('admin.parametrage.type-materiel.store')->middleware('permission:5');
    Route::patch('/admin/parametrage/type-materiel/{id}', [ParametrageController::class, 'typeMaterielUpdate'])->name('admin.parametrage.type-materiel.update')->middleware('permission:5');
    Route::delete('/admin/parametrage/type-materiel/{id}', [ParametrageController::class, 'typeMaterielDestroy'])->name('admin.parametrage.type-materiel.destroy')->middleware('permission:5');
    // Type consommable
    Route::get('/admin/parametrage/type-consommable', [ParametrageController::class, 'typeConsommableIndex'])->name('admin.parametrage.type-consommable')->middleware('permission:5');
    Route::post('/admin/parametrage/type-consommable', [ParametrageController::class, 'typeConsommableStore'])->name('admin.parametrage.type-consommable.store')->middleware('permission:5');
    Route::patch('/admin/parametrage/type-consommable/{id}', [ParametrageController::class, 'typeConsommableUpdate'])->name('admin.parametrage.type-consommable.update')->middleware('permission:5');
    Route::delete('/admin/parametrage/type-consommable/{id}', [ParametrageController::class, 'typeConsommableDestroy'])->name('admin.parametrage.type-consommable.destroy')->middleware('permission:5');
    // Type véhicule
    Route::get('/admin/parametrage/type-vehicule', [ParametrageController::class, 'typeVehiculeIndex'])->name('admin.parametrage.type-vehicule')->middleware('permission:5');
    Route::post('/admin/parametrage/type-vehicule', [ParametrageController::class, 'typeVehiculeStore'])->name('admin.parametrage.type-vehicule.store')->middleware('permission:5');
    Route::patch('/admin/parametrage/type-vehicule/{code}', [ParametrageController::class, 'typeVehiculeUpdate'])->name('admin.parametrage.type-vehicule.update')->middleware('permission:5');
    Route::delete('/admin/parametrage/type-vehicule/{code}', [ParametrageController::class, 'typeVehiculeDestroy'])->name('admin.parametrage.type-vehicule.destroy')->middleware('permission:5');
    // Habilitations — full ACL: section ceilings + group/role grants + per-user overrides
    Route::get('/admin/habilitations', [HabilitationController::class, 'index'])->name('admin.habilitations')->middleware('permission:9');
    Route::post('/admin/habilitations/grant', [HabilitationController::class, 'setGrant'])->name('admin.habilitations.grant.set')->middleware('permission:9');
    Route::post('/admin/habilitations/derogation', [HabilitationController::class, 'setUserGrant'])->name('admin.habilitations.user.set')->middleware('permission:9');
    Route::post('/admin/habilitations/plafond', [HabilitationController::class, 'toggleCeiling'])->name('admin.habilitations.ceiling.toggle')->middleware('permission:9');
    Route::post('/admin/habilitations/groupe', [HabilitationController::class, 'groupStore'])->name('admin.habilitations.group.store')->middleware('permission:9');
    Route::patch('/admin/habilitations/groupe/{gpId}', [HabilitationController::class, 'groupUpdate'])->name('admin.habilitations.group.update')->middleware('permission:9');
    Route::delete('/admin/habilitations/groupe/{gpId}', [HabilitationController::class, 'groupDestroy'])->name('admin.habilitations.group.destroy')->middleware('permission:9');
    // Active section / role context switch (navbar)
    Route::get('/contexte/section', [ContextController::class, 'section'])->name('context.section')->middleware('permission:0');
    Route::get('/contexte/role', [ContextController::class, 'role'])->name('context.role')->middleware('permission:0');
    // User-facing "Mes droits" (effective permissions preview)
    Route::get('/mes-droits', [MesDroitsController::class, 'index'])->name('mes-droits')->middleware('permission:0');
    // Grade icons
    Route::get('/admin/parametrage/grade', [ParametrageController::class, 'gradeIndex'])->name('admin.parametrage.grade')->middleware('permission:5');
    Route::post('/admin/parametrage/grade/{grade}/icon', [ParametrageController::class, 'gradeIconUpload'])->name('admin.parametrage.grade.icon.upload')->middleware('permission:5');
    Route::delete('/admin/parametrage/grade/{grade}/icon', [ParametrageController::class, 'gradeIconDestroy'])->name('admin.parametrage.grade.icon.destroy')->middleware('permission:5');
    Route::middleware('feature:cotisations')->group(function () {
        Route::get('/cotisations', [CotisationController::class, 'index'])->name('cotisations.index')->middleware('permission:53');
        Route::post('/cotisations', [CotisationController::class, 'batchSave'])->name('cotisations.save')->middleware('permission:53');
        Route::get('/cotisations/export', [CotisationController::class, 'export'])->name('cotisations.export')->middleware('permission:53');
        Route::get('/cotisations/prelevements', [CotisationController::class, 'prelevements'])->name('cotisations.prelevements')->middleware('permission:53');
        Route::post('/cotisations/prelevements', [CotisationController::class, 'savePrelevements'])->name('cotisations.prelevements.save')->middleware('permission:53');
        Route::get('/cotisations/virements', [CotisationController::class, 'virements'])->name('cotisations.virements')->middleware('permission:53');
    });
    Route::get('/planning', [PlanningController::class, 'index'])->name('planning.index')->middleware('permission:0');
    Route::middleware('feature:vehicules')->group(function () {
        Route::get('/vehicules', [VehiculeController::class, 'index'])->name('vehicule.index')->middleware('permission:42');
        Route::get('/vehicules/create', [VehiculeController::class, 'create'])->name('vehicule.create')->middleware('permission:17');
        Route::post('/vehicules', [VehiculeController::class, 'store'])->name('vehicule.store')->middleware('permission:17');
        Route::get('/vehicules/{vehicule}', [VehiculeController::class, 'show'])->name('vehicule.show')->middleware('permission:42');
        Route::get('/vehicules/{vehicule}/edit', [VehiculeController::class, 'edit'])->name('vehicule.edit')->middleware('permission:17');
        Route::put('/vehicules/{vehicule}', [VehiculeController::class, 'update'])->name('vehicule.update')->middleware('permission:17');
        Route::delete('/vehicules/{vehicule}', [VehiculeController::class, 'destroy'])->name('vehicule.destroy')->middleware('permission:19');
    });
    Route::get('/materiels', [MaterielController::class, 'index'])->name('materiel.index')->middleware(['permission:42', 'feature:materiel']);
    Route::get('/consommables', [ConsommableController::class, 'index'])->name('consommable.index')->middleware(['permission:42', 'feature:consommables']);
    Route::get('/documents', [DocumentController::class, 'index'])->name('document.index')->middleware('permission:44');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('document.download')->middleware('permission:44');
    Route::get('/documents/export/{format}', [DocumentController::class, 'export'])->name('document.export')->middleware('permission:44');
    // Per-object ACL ("Partager") — entry needs library access (44); the SHARE
    // right (or permission 47) is enforced in the controller.
    Route::get('/documents/acl/{type}/{id}', [DocumentAclController::class, 'show'])->name('document.acl')->middleware('permission:44')->whereIn('type', ['folder', 'document']);
    Route::post('/documents/acl/{type}/{id}', [DocumentAclController::class, 'store'])->name('document.acl.store')->middleware('permission:44')->whereIn('type', ['folder', 'document']);
    Route::delete('/documents/acl/ace/{ace}', [DocumentAclController::class, 'destroy'])->name('document.acl.destroy')->middleware('permission:44');
    // Document type & security configuration — permission 47
    Route::get('/documents/types', [DocumentTypeController::class, 'index'])->name('document.types')->middleware('permission:47');
    Route::post('/documents/types', [DocumentTypeController::class, 'store'])->name('document.types.store')->middleware('permission:47');
    Route::patch('/documents/types/{type}', [DocumentTypeController::class, 'update'])->name('document.types.update')->middleware('permission:47');
    Route::delete('/documents/types/{type}', [DocumentTypeController::class, 'destroy'])->name('document.types.destroy')->middleware('permission:47');
    // Folder management (replaces save_folder.php / upd_folder.php). Entry needs
    // library access (44); the per-object ACL (or permission 47) is enforced in
    // the controller, so an ACL grant can authorise a non-manager.
    Route::post('/documents/folders', [DocumentController::class, 'folderStore'])->name('document.folder.store')->middleware('permission:44');
    Route::patch('/documents/folders/{folder}', [DocumentController::class, 'folderUpdate'])->name('document.folder.update')->middleware('permission:44');
    Route::delete('/documents/folders/{folder}', [DocumentController::class, 'folderDestroy'])->name('document.folder.destroy')->middleware('permission:44');
    // Document upload / edit / delete (replaces upd_document.php / save_documents.php).
    Route::post('/documents', [DocumentController::class, 'store'])->name('document.store')->middleware('permission:44');
    Route::patch('/documents/{document}', [DocumentController::class, 'update'])->name('document.update')->middleware('permission:44');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('document.destroy')->middleware('permission:44');
    Route::get('/messages', [MessageController::class, 'index'])->name('message.index')->middleware('permission:44');
    Route::get('/organisation', fn () => redirect()->route('organisation.organigramme'))->name('organisation.index');
    Route::get('/organisation/organigramme', [OrganisationController::class, 'index'])->name('organisation.organigramme')->middleware('permission:52');
    // Sections — native list + CRUD (replaces departement.php)
    Route::get('/organisation/sections', [OrganisationController::class, 'sections'])->name('organisation.sections')->middleware('permission:52');
    Route::get('/organisation/sections/create', [OrganisationController::class, 'createSection'])->name('organisation.sections.create')->middleware('permission:52');
    Route::post('/organisation/sections', [OrganisationController::class, 'storeSection'])->name('organisation.sections.store')->middleware('permission:52');
    Route::get('/organisation/sections/{section}', [OrganisationController::class, 'showSection'])->name('organisation.sections.show')->middleware('permission:52');
    Route::get('/organisation/sections/{section}/edit', [OrganisationController::class, 'editSection'])->name('organisation.sections.edit')->middleware('permission:52');
    Route::patch('/organisation/sections/{section}', [OrganisationController::class, 'updateSection'])->name('organisation.sections.update')->middleware('permission:52');
    Route::delete('/organisation/sections/{section}', [OrganisationController::class, 'destroySection'])->name('organisation.sections.destroy')->middleware('permission:52');
    Route::patch('/organisation/sections/{section}/personalisation', [OrganisationController::class, 'updatePersonalisation'])->name('organisation.sections.personalisation')->middleware('permission:52');
    // PDF assets — permission:0 because any member generating a livret/carte needs them
    Route::get('/organisation/sections/{section}/letterhead', [OrganisationController::class, 'sectionLetterhead'])->name('organisation.sections.letterhead')->middleware('permission:0');
    Route::delete('/organisation/sections/{section}/letterhead', [OrganisationController::class, 'resetLetterhead'])->name('organisation.sections.letterhead.reset')->middleware('permission:52');
    Route::get('/organisation/sections/{section}/badge', [OrganisationController::class, 'sectionBadge'])->name('organisation.sections.badge')->middleware('permission:0');
    Route::delete('/organisation/sections/{section}/badge', [OrganisationController::class, 'resetBadge'])->name('organisation.sections.badge.reset')->middleware('permission:52');
    Route::patch('/organisation/sections/{section}/rib', [OrganisationController::class, 'updateRib'])->name('organisation.sections.rib')->middleware('permission:52');
    Route::put('/organisation/sections/{section}/agrement/{code}', [OrganisationController::class, 'upsertAgrement'])->name('organisation.sections.agrement.upsert')->middleware('permission:52');
    Route::delete('/organisation/sections/{section}/agrement/{code}', [OrganisationController::class, 'destroyAgrement'])->name('organisation.sections.agrement.destroy')->middleware('permission:52');
    // Cartographie — native Leaflet map (replaces jvectormap.php)
    Route::get('/organisation/cartographie', [OrganisationController::class, 'cartographie'])->name('organisation.cartographie')->middleware(['permission:27', 'feature:carte']);
    Route::get('/statistiques', fn () => redirect()->route('statistique.dashboard'))->name('statistique.index');
    Route::get('/statistiques/dashboard', [StatistiqueController::class, 'index'])->name('statistique.dashboard')->middleware('permission:27');
    Route::get('/statistiques/bilan-annuel', fn () => redirect()->route('statistique.bilan.generalites'))->name('statistique.bilan');
    Route::get('/statistiques/bilan-annuel/generalites', [StatistiqueController::class, 'bilanGeneralites'])->name('statistique.bilan.generalites')->middleware('permission:27');
    Route::get('/statistiques/bilan-annuel/activites', [StatistiqueController::class, 'bilanActivites'])->name('statistique.bilan.activites')->middleware('permission:27');
    Route::get('/statistiques/bilan-annuel/formations', [StatistiqueController::class, 'bilanFormations'])->name('statistique.bilan.formations')->middleware('permission:27');
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
    Route::get('personnel/{personnel}/livret-data', [PersonnelController::class, 'livretData'])
        ->name('personnel.livret')->middleware('permission:0');
    Route::get('personnel/{personnel}/carte-data', [PersonnelController::class, 'carteData'])
        ->name('personnel.carte')->middleware('permission:0');
    // Géolocalisation
    Route::get('/geolocalisation', [GeolocalisationController::class, 'index'])
        ->name('geolocalisation.index')->middleware(['permission:0', 'feature:geolocalize_enabled']);
    Route::post('personnel/{personnel}/gps', [GeolocalisationController::class, 'updateGps'])
        ->name('personnel.gps.update')->middleware('permission:0');
    Route::get('/trombinoscope', [PersonnelController::class, 'trombinoscope'])->name('personnel.trombinoscope')->middleware('permission:0');
    Route::get('/qualifications', [PersonnelController::class, 'qualifications'])->name('personnel.qualifications')->middleware(['permission:56', 'feature:competences']);
    Route::get('/clients', [CompanyController::class, 'index'])->name('company.index')->middleware(['permission:29', 'feature:client']);
    Route::get('/legacy', fn () => redirect()->route('dashboard'))->name('dashboard.legacy');
    Route::get('/about', function () {
        // TODO: Migrate code
        return redirect('/legacy/about.php');
    })->name('about');
    Route::post('/shortcuts/toggle', [ShortcutController::class, 'toggle'])->name('shortcuts.toggle');

    // Account — combined authentication page (password + 2FA)
    Route::get('/account/authentification', [AccountController::class, 'showAuth'])->name('account.auth');
    Route::post('/account/authentification', [AccountController::class, 'changePassword'])->name('account.password.update');
    Route::post('/account/authentification/2fa/confirm', [TotpController::class, 'confirmSetup'])->name('totp.confirm');
    Route::post('/account/authentification/2fa/codes', [TotpController::class, 'regenerateCodes'])->name('totp.codes.regenerate');
    Route::delete('/account/authentification/2fa', [TotpController::class, 'disable'])->name('totp.disable');
    // Legacy redirects
    Route::redirect('/account/password', '/account/authentification')->name('account.password');
    Route::redirect('/account/2fa', '/account/authentification?tab=2fa')->name('totp.setup');

    // Account — charter acceptance
    Route::get('/account/charter', [AccountController::class, 'showCharter'])->name('account.charter');
    Route::post('/account/charter/accept', [AccountController::class, 'acceptCharter'])->name('account.charter.accept');
    Route::post('/account/charter/reject', [AccountController::class, 'rejectCharter'])->name('account.charter.reject');
    Route::post('/account/charter/reset', [AccountController::class, 'resetCharter'])->name('account.charter.reset');

    // Admin — charter editor (permission 14), nested under security
    Route::get('/admin/security/charter', [AccountController::class, 'showEditCharter'])->name('admin.security.charter')->middleware('permission:14');
    Route::post('/admin/security/charter', [AccountController::class, 'saveCharter'])->name('admin.security.charter.save')->middleware('permission:14');

    // Connected users (permission 20 = Audit)
    Route::get('/admin/connected-users', [AccountController::class, 'connectedUsers'])->name('account.connected-users')->middleware('permission:20');

    // Send credentials — admin action, nested under personnel
    Route::get('personnel/{personnel}/send-credentials', [AccountController::class, 'showSendCredentials'])->name('personnel.send-credentials.show');
    Route::post('personnel/{personnel}/send-credentials', [AccountController::class, 'sendCredentials'])->name('personnel.send-credentials');

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

if (file_exists(__DIR__.'/web_legacy_bridge.php')) {
    require __DIR__.'/web_legacy_bridge.php';
}
