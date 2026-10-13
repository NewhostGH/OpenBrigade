<?php

namespace App\Services\Api;

use App\Exceptions\ApiException;
use App\Support\Audit;
use Illuminate\Support\Facades\DB;

/**
 * POST /api/v1/personnel/import: port of the legacy api/import/people.php.
 *
 * `action` = ImportPersonnel creates a member, UpdatePersonnel rewrites the
 * member `P_ID` (whose P_NOM must match, as a safety check). When a
 * `competences` list is sent, it replaces all the member's competences.
 * Validation order and errnum codes follow the legacy script.
 */
class PersonnelImportService
{
    private const DATE = 'Y-m-d';

    /**
     * @param  array<string,mixed>  $data
     * @return array{id:int,created:bool,message:string}
     */
    public function import(array $data): array
    {
        $in = new ApiInput($data);

        $action = $in->string('action', 1050);
        $in->failIf(! in_array($action, ['ImportPersonnel', 'UpdatePersonnel'], true), 1051, "Invalid action_code: {$action}");

        $row = $this->validated($in);
        $competences = $this->competences($in);
        $id = $in->int('P_ID');

        return DB::transaction(function () use ($action, $row, $competences, $id) {
            if ($action === 'ImportPersonnel') {
                $this->assertNoDuplicate($row);
                $id = $this->insert($row);
                $result = ['id' => $id, 'created' => true, 'message' => "Insert successful, new user created, number {$id}"];
                Audit::activity('api.personnel_created', ['target' => $id]);
            } else {
                $this->assertUpdatable($id, $row);
                DB::table('pompier')->where('P_ID', $id)->update($row);
                $result = ['id' => $id, 'created' => false, 'message' => "Update successful, for user {$id}"];
                Audit::activity('api.personnel_updated', ['target' => $id]);
            }

            if ($competences !== null) {
                $this->replaceCompetences($id, $competences);
            }

            return $result;
        });
    }

    /** @return array<string,mixed> pompier columns */
    private function validated(ApiInput $in): array
    {
        $code = str_replace("'", '', $in->name('P_CODE', 20, 1061, 1060, digits: true));
        $in->failIf($code === '', 1061, 'Invalid P_CODE (maximum 20 characters)');

        $statut = $in->string('P_STATUT', 1065);
        $in->failIf($statut === '' || mb_strlen($statut) > 5, 1066, 'Invalid P_STATUT (maximum 5 characters)');
        $in->failIf(! DB::table('statut')->where('S_STATUT', $statut)->exists(), 1067, "Invalid P_STATUT {$statut}, it does not exist in the database");

        $nom = mb_strtolower($in->name('P_NOM', 30, 1071, 1070));
        $in->failIf($nom === '', 1071, 'Invalid P_NOM (maximum 30 characters)');
        $prenom = $in->name('P_PRENOM', 25, 1081, 1080);
        $in->failIf($prenom === '', 1081, 'Invalid P_PRENOM (maximum 25 characters)');
        $prenom2 = mb_strtolower($in->name('P_PRENOM2', 25, 1082));
        $nomNaissance = mb_strtolower($in->name('P_NOM_NAISSANCE', 30, 1084));

        $birthdate = $in->date('P_BIRTHDATE', self::DATE, 1091, 1090);
        $in->failIf($birthdate === '', 1091, "P_BIRTHDATE is invalid expected format is '".self::DATE."'");
        $birthplace = mb_strtolower($in->name('P_BIRTHPLACE', 40, 1092));
        $birthdep = $in->name('P_BIRTHDEP', 3, 1093, digits: true);

        $sexe = $in->string('P_SEXE', 1100);
        $in->failIf(! in_array($sexe, ['M', 'F'], true), 1101, 'Invalid P_SEXE (M or F)');

        $civilite = $in->int('P_CIVILITE', 1110);
        $in->failIf(! in_array($civilite, [1, 2, 3], true), 1111, 'Invalid P_CIVILITE (1,2 or 3)');

        $password = $in->string('P_MDP');
        $in->failIf($password !== '' && ! preg_match('/^[a-f0-9]{32}$/', $password), 1115, 'Invalid P_MDP, expected md5 encoded string (32 characters)');

        $engagement = $in->date('P_DATE_ENGAGEMENT', self::DATE, 1121, 1120);
        $in->failIf($engagement === '', 1121, "P_DATE_ENGAGEMENT is invalid expected format is '".self::DATE."'");

        $section = $in->string('P_SECTION', 1130);
        $in->failIf(! ctype_digit($section), 1131, "Invalid P_SECTION {$section} must be numeric");
        $in->failIf(! DB::table('section')->where('S_ID', (int) $section)->exists(), 1132, "Invalid P_SECTION {$section} it does not exist");

        $email = $in->email('P_EMAIL', 1140);
        $phone = $in->phone('P_PHONE', 1150);
        $phone2 = $in->phone('P_PHONE2', 1151);
        $address = $in->string('P_ADDRESS');
        $in->failIf(mb_strlen($address) > 150, 1160, 'Invalid P_ADDRESS (maximum 150 characters)');
        $zip = $in->string('P_ZIP_CODE');
        $in->failIf($zip !== '' && (mb_strlen($zip) > 6 || ! preg_match('/^\d[\dA-Za-z ]*$/', $zip)), 1170, 'Invalid P_ZIP_CODE');
        $city = $in->name('P_CITY', 30, 1180, digits: true);

        $relPrenom = $in->name('P_RELATION_PRENOM', 20, 1200);
        $relNom = $in->name('P_RELATION_NOM', 30, 1201);
        $relPhone = $in->phone('P_RELATION_PHONE', 1210);
        $relMail = $in->email('P_RELATION_MAIL', 1220);

        $pays = $in->int('P_PAYS', default: 65);
        $in->failIf($pays <= 0, 1230, "Invalid P_PAYS {$pays} must be numeric");
        $in->failIf(! DB::table('pays')->where('ID', $pays)->exists(), 1231, "Invalid P_PAYS {$pays}, it does not exist");

        $grade = $in->has('P_GRADE') ? $in->string('P_GRADE') : '-';
        $in->failIf(mb_strlen($grade) > 6, 1240, 'Invalid P_GRADE (maximum 6 characters)');
        $in->failIf(! DB::table('grade')->where('G_GRADE', $grade)->exists(), 1241, "Invalid P_GRADE {$grade}, it does not exist");

        $row = [
            'P_CODE' => $code,
            'P_STATUT' => $statut,
            'P_NOM' => $nom,
            'P_PRENOM' => $prenom,
            'P_PRENOM2' => $prenom2,
            'P_NOM_NAISSANCE' => $nomNaissance,
            'P_BIRTHDATE' => $birthdate,
            'P_BIRTHPLACE' => $birthplace,
            'P_BIRTH_DEP' => $birthdep,
            'P_SEXE' => $sexe,
            'P_CIVILITE' => $civilite,
            'P_DATE_ENGAGEMENT' => $engagement,
            'P_SECTION' => (int) $section,
            'P_EMAIL' => $email,
            'P_PHONE' => $phone,
            'P_PHONE2' => $phone2,
            'P_ADDRESS' => $address,
            'P_ZIP_CODE' => $zip,
            'P_CITY' => mb_strtoupper($city),
            'P_RELATION_PRENOM' => $relPrenom,
            'P_RELATION_NOM' => $relNom,
            'P_RELATION_PHONE' => $relPhone,
            'P_RELATION_MAIL' => $relMail,
            'P_PAYS' => $pays,
            'P_GRADE' => $grade,
        ];

        // An omitted password never overwrites the existing one on update.
        if ($password !== '') {
            $row['P_MDP'] = $password;
        }

        return $row;
    }

    /**
     * Validated competences list, or null when the payload has none (or an empty list).
     *
     * @return list<array{id:int,expiration:?string}>|null
     */
    private function competences(ApiInput $in): ?array
    {
        if (! $in->has('competences')) {
            return null;
        }

        $list = $in->raw('competences');
        $in->failIf(! is_array($list), 1300, 'Wrong value for competences, expected a list');

        $out = [];
        foreach ($list as $competence) {
            $raw = is_array($competence) ? ($competence['id'] ?? '') : '';
            $id = (int) $raw;
            $in->failIf($id <= 0, 1300, 'Wrong value for competence '.(is_scalar($raw) ? $raw : '').' must be numeric');
            $in->failIf(! DB::table('poste')->where('PS_ID', $id)->exists(), 1301, "Wrong value for competence {$id} not found");

            $expiration = isset($competence['expiration']) && is_scalar($competence['expiration']) ? trim((string) $competence['expiration']) : null;
            if ($expiration !== null && $expiration !== '') {
                ApiInput::assertDate($expiration, self::DATE, 1302, 'expiration date');
            }
            $out[$id] = ['id' => $id, 'expiration' => $expiration ?: null];
        }

        // Like the legacy script, an empty list leaves the competences untouched.
        return $out === [] ? null : array_values($out);
    }

    /** @param array<string,mixed> $row */
    private function assertNoDuplicate(array $row): void
    {
        $twin = DB::table('pompier')
            ->where('P_NOM', $row['P_NOM'])
            ->where('P_PRENOM', $row['P_PRENOM'])
            ->where(fn ($q) => $q->whereNull('P_BIRTHDATE')->orWhere('P_BIRTHDATE', $row['P_BIRTHDATE']))
            ->value('P_ID');

        if ($twin) {
            throw new ApiException(1400, "You are trying to insert a duplicate Name,Firstname,Birthdate see this person: {$twin}", 409);
        }

        $this->assertCodeFree($row['P_CODE']);
    }

    /** @param array<string,mixed> $row */
    private function assertUpdatable(int $id, array $row): void
    {
        if ($id <= 0) {
            throw new ApiException(1430, 'Error invalid P_ID, must be provided and numeric');
        }
        if (! DB::table('pompier')->where('P_ID', $id)->exists()) {
            throw new ApiException(1431, "Wrong value for P_ID, {$id} not found in the database.", 404);
        }
        if (! DB::table('pompier')->where('P_ID', $id)->where('P_NOM', $row['P_NOM'])->exists()) {
            throw new ApiException(1432, "This provided P_ID,{$id} does not match the name P_NOM {$row['P_NOM']}", 409);
        }

        $this->assertCodeFree($row['P_CODE'], $id);
    }

    private function assertCodeFree(string $code, int $except = 0): void
    {
        $other = DB::table('pompier')->where('P_CODE', $code)->where('P_ID', '<>', $except)->value('P_ID');

        if ($other) {
            throw new ApiException(1400, "You are trying to insert a duplicate see this person: {$other}", 409);
        }
    }

    /** @param array<string,mixed> $row */
    private function insert(array $row): int
    {
        // External members get no access (legacy GP_ID = -1); others the default group.
        $group = $row['P_STATUT'] === 'EXT' ? -1 : 0;

        return (int) DB::table('pompier')->insertGetId($row + [
            'P_MDP' => '',
            'GP_ID' => $group,
            'GP_ID2' => $group,
            'P_HIDE' => 1,
            'P_CREATE_DATE' => now()->toDateString(),
        ], 'P_ID');
    }

    /** @param list<array{id:int,expiration:?string}> $competences */
    private function replaceCompetences(int $id, array $competences): void
    {
        DB::table('qualification')->where('P_ID', $id)->delete();

        DB::table('qualification')->insert(array_map(fn (array $c) => [
            'P_ID' => $id,
            'PS_ID' => $c['id'],
            'Q_VAL' => 1,
            'Q_EXPIRATION' => $c['expiration'],
            'Q_UPDATED_BY' => null,
            'Q_UPDATE_DATE' => now(),
        ], $competences));
    }
}
