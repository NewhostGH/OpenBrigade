<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Group;
use App\Models\LegacyFeature;
use App\Models\Personnel;
use App\Models\Section;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DevelopmentDataSeeder extends Seeder
{
    public function run(): void
    {
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

        $groupe = Group::query()->updateOrCreate(
            ['GP_ID' => 900],
            [
                'GP_DESCRIPTION' => 'DEV_MANAGER',
                'TR_CONFIG' => 1,
                'TR_SUB_POSSIBLE' => 1,
                'TR_ALL_POSSIBLE' => 0,
                'TR_WIDGET' => 1,
                'GP_USAGE' => 'internes',
                'GP_ASTREINTE' => 0,
                'GP_ORDER' => 10,
            ]
        );

        $fonctionnalite = LegacyFeature::query()->updateOrCreate(
            ['F_ID' => 9000],
            [
                'F_LIBELLE' => 'DEV_PANEL',
                'F_TYPE' => 0,
                'TF_ID' => 0,
                'F_FLAG' => 0,
                'F_DESCRIPTION' => 'Development-only dashboard permission',
            ]
        );

        DB::table('habilitation')->updateOrInsert(
            ['GP_ID' => $groupe->GP_ID, 'F_ID' => $fonctionnalite->F_ID],
            ['GP_ID' => $groupe->GP_ID, 'F_ID' => $fonctionnalite->F_ID]
        );

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
                'GP_ID' => $groupe->GP_ID,
                'GP_ID2' => 0,
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

        if (Personnel::query()->where('P_CODE', 'like', 'dev.user%')->count() === 0) {
            $sequence = 1000;
            Personnel::factory()
                ->count(5)
                ->state(function () use (&$sequence, $section, $groupe): array {
                    $code = 'dev.user.'.$sequence;
                    $sequence++;

                    return [
                        'P_SECTION' => $section->S_ID,
                        'GP_ID' => $groupe->GP_ID,
                        'P_CODE' => $code,
                    ];
                })
                ->create();
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
