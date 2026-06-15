<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Personnel;
use App\Models\Section;
use App\Support\Habilitations\BaseHabilitations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Throwaway fixtures for local development and tests only (never production —
 * gated in {@see DatabaseSeeder}). The canonical habilitation data and the
 * super-admin account are owned by {@see CoreSeeder}; this seeder only attaches
 * dev personnel to the already-seeded base groups/roles.
 */
class DevelopmentDataSeeder extends Seeder
{
    public function run(): void
    {
        $base = new BaseHabilitations;
        $adminGroupId = (int) collect(config('habilitations.base_groups'))->search(fn ($d) => $d['default'] === 'admin');
        $userGroupId = (int) collect(config('habilitations.base_groups'))->search(fn ($d) => $d['default'] === 'user');
        $chefRoleId = $base->roleId(0, 0); // generic "Chef de section"

        // Deterministic core records used as anchors in local development.
        $section = Section::query()->updateOrCreate(
            ['S_ID' => 900],
            [
                'S_PARENT' => 0,
                'S_CODE' => 'DEV-HQ',
                'S_DESCRIPTION' => 'Development HQ',
                'S_HIDE' => 0,
                'S_INACTIVE' => 0,
                'S_ORDER' => 900,
                'SHOW_PHONE3' => 1,
                'SHOW_EMAIL3' => 1,
                'SHOW_URL' => 1,
                'S_TIMEZONE' => 'Europe/Paris',
                'NB_DAYS_BEFORE_BLOCK' => 0,
                'SMS_LOCAL_PROVIDER' => 0,
            ]
        );

        // Dev manager → Admin group + Chef of the dev section.
        $manager = Personnel::query()->updateOrCreate(
            ['P_CODE' => 'dev.manager'],
            [
                'P_PRENOM' => 'Dev',
                'P_NOM' => 'Manager',
                'P_SEXE' => 'M',
                'P_CIVILITE' => 1,
                'P_OLD_MEMBER' => 0,
                'P_GRADE' => '-',
                'P_PROFESSION' => 'SPP',
                'P_STATUT' => 'SPV',
                'P_MDP' => Hash::make('password'),
                'P_SECTION' => $section->S_ID,
                'C_ID' => 0,
                'GP_ID' => $adminGroupId,
                'GP_ID2' => 0,
                'P_SUPERADMIN' => 0,
                'P_EMAIL' => 'dev.manager@openbrigade.local',
                'P_HIDE' => 0,
                'P_NB_CONNECT' => 0,
                'GP_FLAG1' => 0,
                'GP_FLAG2' => 0,
                'P_NOSPAM' => 0,
                'TP_ID' => 0,
                'NPAI' => 0,
                'SUSPENDU' => 0,
                'MONTANT_REGUL' => 0,
                'P_MAITRE' => 0,
            ]
        );
        DB::table('ob_personnel_group')->insertOrIgnore(['person_id' => $manager->P_ID, 'group_id' => $adminGroupId]);
        DB::table('ob_user_assignment')->insertOrIgnore([
            'person_id' => $manager->P_ID, 'section_id' => $section->S_ID, 'group_id' => $chefRoleId,
        ]);

        Event::query()->updateOrCreate(
            ['E_CODE' => 900000],
            [
                'TE_CODE' => 'FOR',
                'S_ID' => $section->S_ID,
                'E_CHEF' => $manager->P_ID,
                'E_LIBELLE' => 'Development training event',
                'E_LIEU' => 'OpenBrigade Lab',
                'E_NB_DPS' => 0,
                'E_OPEN_TO_EXT' => 0,
                'E_CLOSED' => 0,
                'E_CANCELED' => 0,
                'E_MAIL1' => 0,
                'E_MAIL2' => 0,
                'E_MAIL3' => 0,
                'E_ALLOW_REINFORCEMENT' => 0,
                'TAV_ID' => 1,
                'E_FLAG1' => 0,
                'E_VISIBLE_OUTSIDE' => 0,
                'E_REPAS' => 0,
                'E_TRANSPORT' => 0,
                'E_PARTIES' => 1,
                'E_EQUIPE' => 0,
                'E_VISIBLE_INSIDE' => 1,
                'E_EXTERIEUR' => 0,
                'E_COLONNE_RENFORT' => 0,
                'E_ANOMALIE' => 0,
            ]
        );

        // Dev members → User group.
        if (Personnel::query()->where('P_CODE', 'like', 'dev.user%')->count() === 0) {
            $sequence = 1000;
            $users = Personnel::factory()
                ->count(5)
                ->state(function () use (&$sequence, $section, $userGroupId): array {
                    $code = 'dev.user.'.$sequence;
                    $sequence++;

                    return [
                        'P_SECTION' => $section->S_ID,
                        'GP_ID' => $userGroupId,
                        'P_CODE' => $code,
                    ];
                })
                ->create();

            foreach ($users as $u) {
                DB::table('ob_personnel_group')->insertOrIgnore(['person_id' => $u->P_ID, 'group_id' => $userGroupId]);
            }
        }

        if (Event::query()->where('E_CODE', '>=', 900001)->where('E_CODE', '<=', 900099)->count() === 0) {
            $codes = [900001, 900002, 900003];
            foreach ($codes as $code) {
                Event::factory()->create([
                    'E_CODE' => $code,
                    'S_ID' => $section->S_ID,
                    'E_CHEF' => $manager->P_ID,
                ]);
            }
        }
    }
}
