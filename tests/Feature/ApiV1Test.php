<?php

use App\Services\GeneralSettingService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

const API_EXPORT_TOKEN = 'export-secret';
const API_IMPORT_TOKEN = 'import-secret';

beforeEach(function () {
    Schema::create('configuration', function (Blueprint $t) {
        $t->integer('ID')->primary();
        $t->string('NAME');
        $t->string('VALUE')->nullable();
    });
    Schema::create('pompier', function (Blueprint $t) {
        $t->increments('P_ID');
        $t->string('P_CODE', 20)->unique();
        $t->string('P_PRENOM', 25);
        $t->string('P_PRENOM2', 25)->nullable();
        $t->string('P_NOM', 30);
        $t->string('P_NOM_NAISSANCE', 30)->nullable();
        $t->string('P_SEXE', 1)->default('M');
        $t->tinyInteger('P_CIVILITE')->default(1);
        $t->tinyInteger('P_OLD_MEMBER')->default(0);
        $t->string('P_GRADE', 6)->default('-');
        $t->string('P_STATUT', 5)->default('SPV');
        $t->string('P_MDP')->default('');
        $t->date('P_DATE_ENGAGEMENT')->nullable();
        $t->integer('P_SECTION')->nullable();
        $t->integer('GP_ID')->default(0);
        $t->integer('GP_ID2')->default(0);
        $t->date('P_BIRTHDATE')->nullable();
        $t->string('P_BIRTHPLACE', 40)->nullable();
        $t->string('P_BIRTH_DEP', 3)->nullable();
        $t->string('P_EMAIL', 60)->nullable();
        $t->string('P_PHONE', 20)->nullable();
        $t->string('P_PHONE2', 20)->nullable();
        $t->string('P_ADDRESS', 150)->nullable();
        $t->string('P_ZIP_CODE', 6)->nullable();
        $t->string('P_CITY', 30)->nullable();
        $t->string('P_RELATION_PRENOM', 20)->nullable();
        $t->string('P_RELATION_NOM', 30)->nullable();
        $t->string('P_RELATION_PHONE', 20)->nullable();
        $t->string('P_RELATION_MAIL', 60)->nullable();
        $t->tinyInteger('P_HIDE')->default(1);
        $t->date('P_CREATE_DATE')->nullable();
        $t->integer('P_PAYS')->nullable();
    });
    Schema::create('statut', fn (Blueprint $t) => $t->string('S_STATUT', 5));
    Schema::create('section', fn (Blueprint $t) => $t->integer('S_ID'));
    Schema::create('pays', fn (Blueprint $t) => $t->integer('ID'));
    Schema::create('grade', fn (Blueprint $t) => $t->string('G_GRADE', 6));
    Schema::create('poste', function (Blueprint $t) {
        $t->integer('PS_ID');
        $t->string('TYPE', 10);
        $t->tinyInteger('PS_FORMATION')->default(0);
    });
    Schema::create('qualification', function (Blueprint $t) {
        $t->integer('P_ID');
        $t->integer('PS_ID');
        $t->string('Q_VAL')->default('1');
        $t->date('Q_EXPIRATION')->nullable();
        $t->integer('Q_UPDATED_BY')->nullable();
        $t->dateTime('Q_UPDATE_DATE')->nullable();
    });
    Schema::create('type_evenement', fn (Blueprint $t) => $t->string('TE_CODE', 5));
    Schema::create('type_formation', fn (Blueprint $t) => $t->string('TF_CODE', 1));
    Schema::create('evenement', function (Blueprint $t) {
        $t->integer('E_CODE')->primary();
        $t->string('TE_CODE', 5);
        $t->integer('S_ID');
        $t->string('E_LIBELLE', 60);
        $t->string('E_LIEU', 50);
        $t->time('E_HEURE_RDV')->nullable();
        $t->integer('E_NB')->nullable();
        $t->text('E_COMMENT')->nullable();
        $t->dateTime('E_CREATE_DATE')->nullable();
        $t->integer('E_CREATED_BY')->nullable();
        $t->integer('C_ID')->nullable();
        $t->string('E_CONTACT_LOCAL', 50)->nullable();
        $t->string('E_CONTACT_TEL', 20)->nullable();
        $t->string('E_ADDRESS')->nullable();
        $t->tinyInteger('E_VISIBLE_OUTSIDE')->default(0);
        $t->float('E_TARIF')->nullable();
        $t->integer('E_NB_STAGIAIRES')->nullable();
        $t->tinyInteger('E_EXTERIEUR')->default(0);
        $t->string('E_URL', 500)->nullable();
        $t->integer('PS_ID')->nullable();
        $t->string('TF_CODE', 1)->nullable();
        $t->string('E_TEL', 15)->nullable();
    });
    Schema::create('evenement_horaire', function (Blueprint $t) {
        $t->integer('E_CODE');
        $t->integer('EH_ID');
        $t->date('EH_DATE_DEBUT');
        $t->date('EH_DATE_FIN');
        $t->time('EH_DEBUT');
        $t->time('EH_FIN');
        $t->float('EH_DUREE');
        $t->string('EH_DESCRIPTION', 20)->nullable();
    });
    Schema::create('evenement_participation', function (Blueprint $t) {
        $t->integer('E_CODE');
        $t->integer('EH_ID');
        $t->integer('P_ID');
        $t->dateTime('EP_DATE')->nullable();
        $t->integer('EP_BY')->nullable();
        $t->integer('TP_ID')->default(0);
        $t->float('EP_DUREE')->nullable();
    });

    DB::table('configuration')->insert([
        ['ID' => 50, 'NAME' => 'webservice_key', 'VALUE' => API_EXPORT_TOKEN],
        ['ID' => 64, 'NAME' => 'import_api', 'VALUE' => '1'],
        ['ID' => 66, 'NAME' => 'import_api_token', 'VALUE' => API_IMPORT_TOKEN],
    ]);
    DB::table('statut')->insert([['S_STATUT' => 'BEN'], ['S_STATUT' => 'EXT']]);
    DB::table('section')->insert([['S_ID' => 0], ['S_ID' => 200]]);
    DB::table('pays')->insert(['ID' => 65]);
    DB::table('grade')->insert(['G_GRADE' => '-']);
    DB::table('poste')->insert([
        ['PS_ID' => 2, 'TYPE' => 'PSE1', 'PS_FORMATION' => 1],
        ['PS_ID' => 19, 'TYPE' => 'PSE2', 'PS_FORMATION' => 1],
    ]);
    DB::table('type_evenement')->insert(['TE_CODE' => 'FOR']);
    DB::table('type_formation')->insert([['TF_CODE' => 'I'], ['TF_CODE' => 'R']]);

    apiSetting();
});

/** Store a configuration value (optional) and serve real, uncached settings. */
function apiSetting(?string $name = null, string $value = ''): void
{
    if ($name !== null) {
        DB::table('configuration')->where('NAME', $name)->update(['VALUE' => $value]);
    }
    app()->instance(GeneralSettingService::class, new GeneralSettingService);
}

function apiPerson(array $overrides = []): int
{
    return (int) DB::table('pompier')->insertGetId($overrides + [
        'P_CODE' => 'jdupont',
        'P_PRENOM' => 'Jeanne',
        'P_NOM' => 'dupont',
        'P_STATUT' => 'BEN',
        'P_SECTION' => 200,
        'P_EMAIL' => 'jeanne@example.org',
        'P_PHONE' => '0630229911',
    ], 'P_ID');
}

function personnelPayload(array $overrides = []): array
{
    return $overrides + [
        'action' => 'ImportPersonnel',
        'P_CODE' => 'mmartin',
        'P_NOM' => 'Martin',
        'P_PRENOM' => 'Marie',
        'P_BIRTHDATE' => '1996-10-15',
        'P_SEXE' => 'F',
        'P_CIVILITE' => '2',
        'P_DATE_ENGAGEMENT' => '2020-11-05',
        'P_SECTION' => '200',
        'P_STATUT' => 'BEN',
        'P_CITY' => "Saint Jean d'Angély",
        'P_RELATION_PHONE' => '061010110',
    ];
}

function eventPayload(array $overrides = []): array
{
    return $overrides + [
        'event_code' => '0',
        'event_name' => 'Formation initiale PSC1',
        'event_type' => 'FOR',
        'location' => 'Bureau',
        'address' => '16 rue de la Serre, 06800 Cagnes sur Mer',
        'section' => '200',
        'competence' => '2',
        'type_formation' => 'I',
        'event_sessions' => [
            ['session_id' => '2', 'start' => '2026-11-18 10:00', 'end' => '2026-11-18 16:00'],
            ['session_id' => '1', 'start' => '2026-11-17 10:15', 'end' => '2026-11-17 18:00'],
        ],
    ];
}

// ── Token guard ──────────────────────────────────────────────────────────────

test('the export API refuses a missing, wrong or disabled token', function () {
    $this->postJson('/api/v1/personnel/search', ['lastname' => 'dup'])
        ->assertStatus(401)->assertJson(['status' => 'error', 'errnum' => 30]);

    $this->postJson('/api/v1/personnel/search', ['token' => 'nope', 'lastname' => 'dup'])
        ->assertStatus(403)->assertJson(['errnum' => 40]);

    apiSetting('webservice_key', '');
    $this->postJson('/api/v1/personnel/search', ['token' => API_EXPORT_TOKEN, 'lastname' => 'dup'])
        ->assertStatus(503)->assertJson(['errnum' => 10]);
});

test('the import API is off unless the import_api switch is set', function () {
    apiSetting('import_api', '0');

    $this->postJson('/api/v1/personnel/import', personnelPayload(['token' => API_IMPORT_TOKEN]))
        ->assertStatus(503)->assertJson(['errnum' => 10]);
});

test('the export token does not open the import API', function () {
    $this->postJson('/api/v1/personnel/import', personnelPayload(['token' => API_EXPORT_TOKEN]))
        ->assertStatus(403);
});

test('a bearer token is accepted and a non-JSON body is refused', function () {
    apiPerson();

    $this->withToken(API_EXPORT_TOKEN)->postJson('/api/v1/personnel/search', ['lastname' => 'dup'])
        ->assertOk()->assertJsonCount(1);

    $this->withToken(API_EXPORT_TOKEN)->post('/api/v1/personnel/search', ['lastname' => 'dup'])
        ->assertStatus(400)->assertJson(['errnum' => 20]);

    // Raw JSON without a JSON Content-Type (curl -d default) is still read.
    $this->call('POST', '/api/v1/personnel/search', [], [], [], ['CONTENT_TYPE' => 'application/x-www-form-urlencoded'],
        json_encode(['token' => API_EXPORT_TOKEN, 'lastname' => 'dup']))
        ->assertOk()->assertJsonCount(1);
});

// ── Personnel search ─────────────────────────────────────────────────────────

test('search returns matching members with their valid competences', function () {
    $id = apiPerson();
    apiPerson(['P_CODE' => 'ext', 'P_NOM' => 'dupond', 'P_STATUT' => 'EXT']);
    DB::table('qualification')->insert([
        ['P_ID' => $id, 'PS_ID' => 2, 'Q_EXPIRATION' => null],
        ['P_ID' => $id, 'PS_ID' => 19, 'Q_EXPIRATION' => '2000-01-01'],
    ]);

    $this->postJson('/api/v1/personnel/search', ['token' => API_EXPORT_TOKEN, 'lastname' => 'DUP'])
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', $id)
        ->assertJsonPath('0.username', 'jdupont')
        ->assertJsonPath('0.section', 200)
        ->assertJsonPath('0.skills', ['2' => 'PSE1']);
});

test('qstrict switches search to exact matches and LIKE wildcards are literal', function () {
    apiPerson();

    $this->postJson('/api/v1/personnel/search', ['token' => API_EXPORT_TOKEN, 'lastname' => 'dup', 'qstrict' => 1])
        ->assertOk()->assertJsonCount(0);
    $this->postJson('/api/v1/personnel/search', ['token' => API_EXPORT_TOKEN, 'lastname' => '%'])
        ->assertOk()->assertJsonCount(0);
    $this->postJson('/api/v1/personnel/search', ['token' => API_EXPORT_TOKEN, 'phone' => '0630'])
        ->assertOk()->assertJsonCount(1);
});

test('search without any criterion is refused', function () {
    $this->postJson('/api/v1/personnel/search', ['token' => API_EXPORT_TOKEN, 'qstrict' => 1])
        ->assertStatus(422)->assertJson(['errnum' => 50]);
});

test('the legacy eBrigade path serves the same search', function () {
    apiPerson();

    $this->postJson('/api/export/search.php', ['token' => API_EXPORT_TOKEN, 'lastname' => 'dup'])
        ->assertOk()->assertJsonCount(1);
});

// ── Personnel import ─────────────────────────────────────────────────────────

test('ImportPersonnel creates a member and its competences', function () {
    $response = $this->postJson('/api/v1/personnel/import', personnelPayload([
        'token' => API_IMPORT_TOKEN,
        'competences' => [['id' => '2', 'expiration' => '2030-12-31'], ['id' => '19']],
    ]))->assertStatus(201)->assertJson(['status' => 'success', 'errnum' => 0]);

    $person = DB::table('pompier')->where('P_ID', $response->json('id'))->first();

    expect($person->P_NOM)->toBe('martin')
        ->and($person->P_CITY)->toBe("SAINT JEAN D'ANGÉLY")
        ->and($person->P_MDP)->toBe('')
        ->and((int) $person->P_HIDE)->toBe(1)
        ->and(DB::table('qualification')->where('P_ID', $person->P_ID)->count())->toBe(2);
});

test('ImportPersonnel refuses duplicates and bad values with the legacy codes', function (array $overrides, int $errnum, int $status) {
    apiPerson(['P_CODE' => 'taken', 'P_NOM' => 'martin', 'P_PRENOM' => 'Paul']);

    $this->postJson('/api/v1/personnel/import', personnelPayload($overrides + ['token' => API_IMPORT_TOKEN]))
        ->assertStatus($status)->assertJson(['status' => 'error', 'errnum' => $errnum]);
})->with([
    'bad action' => [['action' => 'Delete'], 1051, 422],
    'missing statut' => [['P_STATUT' => null], 1065, 422],
    'unknown statut' => [['P_STATUT' => 'XX'], 1067, 422],
    'digits in name' => [['P_NOM' => 'R2D2'], 1071, 422],
    'bad birthdate' => [['P_BIRTHDATE' => '15/10/1996'], 1091, 422],
    'bad sex' => [['P_SEXE' => 'X'], 1101, 422],
    'bad password' => [['P_MDP' => 'plaintext'], 1115, 422],
    'unknown section' => [['P_SECTION' => '9'], 1132, 422],
    'bad email' => [['P_EMAIL' => 'nope'], 1140, 422],
    'bad phone' => [['P_PHONE' => 'abc'], 1150, 422],
    'unknown competence' => [['competences' => [['id' => '99']]], 1301, 422],
    'username taken' => [['P_CODE' => 'taken'], 1400, 409],
]);

test('UpdatePersonnel rewrites a member and replaces competences', function () {
    $id = apiPerson(['P_CODE' => 'old', 'P_NOM' => 'martin', 'P_MDP' => 'keep']);
    DB::table('qualification')->insert(['P_ID' => $id, 'PS_ID' => 19]);

    $this->postJson('/api/v1/personnel/import', personnelPayload([
        'token' => API_IMPORT_TOKEN,
        'action' => 'UpdatePersonnel',
        'P_ID' => (string) $id,
        'competences' => [['id' => '2']],
    ]))->assertOk()->assertJson(['id' => $id]);

    $person = DB::table('pompier')->where('P_ID', $id)->first();

    expect($person->P_CODE)->toBe('mmartin')
        ->and($person->P_MDP)->toBe('keep')
        ->and(DB::table('qualification')->where('P_ID', $id)->pluck('PS_ID')->all())->toEqual([2]);
});

test('UpdatePersonnel checks the target exists and the name matches', function () {
    $id = apiPerson();
    $payload = personnelPayload(['token' => API_IMPORT_TOKEN, 'action' => 'UpdatePersonnel']);

    $this->postJson('/api/v1/personnel/import', $payload)->assertJson(['errnum' => 1430]);
    $this->postJson('/api/v1/personnel/import', $payload + ['P_ID' => 999])->assertStatus(404)->assertJson(['errnum' => 1431]);
    $this->postJson('/api/v1/personnel/import', $payload + ['P_ID' => $id])->assertStatus(409)->assertJson(['errnum' => 1432]);
});

// ── Event import ─────────────────────────────────────────────────────────────

test('an event import creates the activity, its sessions and participants', function () {
    $person = apiPerson();

    $response = $this->postJson('/api/v1/events/import', eventPayload([
        'token' => API_IMPORT_TOKEN,
        'people' => [['user_id' => (string) $person, 'function_id' => '5']],
    ]))->assertStatus(201);

    $code = $response->json('id');
    $sessions = DB::table('evenement_horaire')->where('E_CODE', $code)->orderBy('EH_ID')->get();

    expect(DB::table('evenement')->where('E_CODE', $code)->value('E_LIBELLE'))->toBe('Formation initiale PSC1')
        ->and($sessions)->toHaveCount(2)
        ->and($sessions[0]->EH_DATE_DEBUT)->toBe('2026-11-17')
        ->and((float) $sessions[0]->EH_DUREE)->toBe(7.75)
        ->and(DB::table('evenement_participation')->where('E_CODE', $code)->where('TP_ID', 5)->count())->toBe(2);
});

test('an event import refuses a duplicate and updates an existing activity', function () {
    $code = $this->postJson('/api/v1/events/import', eventPayload(['token' => API_IMPORT_TOKEN]))->json('id');

    $this->postJson('/api/v1/events/import', eventPayload(['token' => API_IMPORT_TOKEN]))
        ->assertStatus(409)->assertJson(['errnum' => 1460]);

    $this->postJson('/api/v1/events/import', eventPayload([
        'token' => API_IMPORT_TOKEN,
        'event_code' => (string) $code,
        'event_name' => 'Recyclage',
        'event_sessions' => [['session_id' => '1', 'start' => '2026-12-01 09:00', 'end' => '2026-12-01 12:00']],
    ]))->assertOk()->assertJson(['id' => $code]);

    expect(DB::table('evenement')->where('E_CODE', $code)->value('E_LIBELLE'))->toBe('Recyclage')
        ->and(DB::table('evenement_horaire')->where('E_CODE', $code)->count())->toBe(1);
});

test('an event import validates sessions, type and references', function (array $overrides, int $errnum) {
    $this->postJson('/api/v1/events/import', eventPayload($overrides + ['token' => API_IMPORT_TOKEN]))
        ->assertJson(['status' => 'error', 'errnum' => $errnum]);
})->with([
    'no sessions' => [['event_sessions' => []], 1210],
    'no session 1' => [['event_sessions' => [['session_id' => '2', 'start' => '2026-11-17 10:00', 'end' => '2026-11-17 11:00']]], 1200],
    'bad start' => [['event_sessions' => [['session_id' => '1', 'start' => '17/11/2026', 'end' => '2026-11-17 11:00']]], 1400],
    'end before start' => [['event_sessions' => [['session_id' => '1', 'start' => '2026-11-17 12:00', 'end' => '2026-11-17 11:00']]], 1420],
    'unordered sessions' => [['event_sessions' => [
        ['session_id' => '1', 'start' => '2026-11-18 10:00', 'end' => '2026-11-18 11:00'],
        ['session_id' => '2', 'start' => '2026-11-17 10:00', 'end' => '2026-11-17 11:00'],
    ]], 1430],
    'unknown type' => [['event_type' => 'XYZ'], 1450],
    'unknown event' => [['event_code' => '42'], 1470],
    'bad url' => [['url' => 'not a url'], 1110],
    'non-training competence' => [['competence' => '7'], 1120],
    'unknown person' => [['people' => [['user_id' => '999', 'function_id' => '1']]], 1320],
]);
