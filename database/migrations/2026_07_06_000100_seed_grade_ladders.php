<?php

// project: OpenBrigade

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fresh installs only ever shipped one placeholder grade ('-', non renseigné) —
 * every category was empty, so an admin had to type in every rank by hand
 * before the grades feature was usable. This seeds a starter ladder per
 * category (highest G_LEVEL = most senior); admins can freely rename, add to,
 * reorder or delete any of it afterwards.
 *
 * Sapeurs-Pompiers, Armée de Terre and Police nationale ladders are the real
 * official rank lists (see fr.wikipedia.org "Liste des grades et
 * appellations des sapeurs-pompiers français", "Grades de l'Armée de terre
 * française", "Grades de la Police nationale française").
 *
 * Organisations that don't map to any of those three (hospital staff,
 * territorial agents, civil security, airport firefighters, or anything
 * else) get a generic "Universel" category (UNIV) instead: 120 numbered
 * levels (Niveau 1..120), seeded fully INACTIVE (G_FLAG=0) so they never
 * clutter a picker by default — an admin renames and activates only the
 * levels their structure actually needs.
 *
 * No custom icons are seeded here — static default icons ship separately
 * under public/images/grades/, named "{CATEGORY}_{CODE}.svg".
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('categorie_grade')->insertOrIgnore([
            ['CG_CODE' => 'POL', 'CG_DESCRIPTION' => 'Police nationale'],
            ['CG_CODE' => 'UNIV', 'CG_DESCRIPTION' => 'Universel (générique)'],
        ]);

        // Superseded by the generic UNIV ladder — hide from pickers, keep any existing assignments.
        DB::table('categorie_grade')->whereIn('CG_CODE', ['HOSP', 'PATS', 'SC', 'SSLIA'])->update(['CG_ACTIVE' => 0]);

        $rows = [
            // Sapeurs-Pompiers (SP)
            ['SAP2', 'Sapeur 2e classe', 10, 'SP'],
            ['SAP1', 'Sapeur 1re classe', 20, 'SP'],
            ['CPL', 'Caporal', 30, 'SP'],
            ['CCH', 'Caporal-chef', 40, 'SP'],
            ['SGT', 'Sergent', 50, 'SP'],
            ['SCH', 'Sergent-chef', 60, 'SP'],
            ['ADJ', 'Adjudant', 70, 'SP'],
            ['ADC', 'Adjudant-chef', 80, 'SP'],
            ['LTN', 'Lieutenant', 90, 'SP'],
            ['CNE', 'Capitaine', 100, 'SP'],
            ['CDT', 'Commandant', 110, 'SP'],
            ['LCL', 'Lieutenant-colonel', 120, 'SP'],
            ['COL', 'Colonel', 130, 'SP'],
            ['CHC', 'Colonel hors classe', 140, 'SP'],
            ['CGL', 'Contrôleur général', 150, 'SP'],
            ['IGL', 'Inspecteur général', 160, 'SP'],

            // Armée de Terre (ARMY)
            ['ASD2', 'Soldat 2e classe', 10, 'ARMY'],
            ['ASD1', 'Soldat 1re classe', 20, 'ARMY'],
            ['ACPL', 'Caporal', 30, 'ARMY'],
            ['ACCH', 'Caporal-chef', 40, 'ARMY'],
            ['ASGT', 'Sergent', 50, 'ARMY'],
            ['ASCH', 'Sergent-chef', 60, 'ARMY'],
            ['AADJ', 'Adjudant', 70, 'ARMY'],
            ['AADC', 'Adjudant-chef', 80, 'ARMY'],
            ['AMAJ', 'Major', 90, 'ARMY'],
            ['AASP', 'Aspirant', 95, 'ARMY'],
            ['ASLT', 'Sous-lieutenant', 100, 'ARMY'],
            ['ALTN', 'Lieutenant', 110, 'ARMY'],
            ['ACNE', 'Capitaine', 120, 'ARMY'],
            ['ACDT', 'Commandant', 130, 'ARMY'],
            ['ALCL', 'Lieutenant-colonel', 140, 'ARMY'],
            ['ACOL', 'Colonel', 150, 'ARMY'],
            ['AGBR', 'Général de brigade', 160, 'ARMY'],
            ['AGDI', 'Général de division', 170, 'ARMY'],
            ['AGCA', "Général de corps d'armée", 180, 'ARMY'],
            ['AGAR', "Général d'armée", 190, 'ARMY'],

            // Police nationale (POL)
            ['PNGAR', 'Gardien de la paix', 10, 'POL'],
            ['PNBRC', 'Brigadier-chef de police', 20, 'POL'],
            ['PNMAJ', 'Major de police', 30, 'POL'],
            ['PNLTN', 'Lieutenant de police', 40, 'POL'],
            ['PNCNE', 'Capitaine de police', 50, 'POL'],
            ['PNCDT', 'Commandant de police', 60, 'POL'],
            ['PNCDD', 'Commandant divisionnaire de police', 70, 'POL'],
            ['PNCOM', 'Commissaire de police', 80, 'POL'],
            ['PNCDV', 'Commissaire divisionnaire de police', 90, 'POL'],
            ['PNCGP', 'Commissaire général de police', 100, 'POL'],
            ['PNDGP', 'Directeur général de la Police nationale', 110, 'POL'],
        ];

        for ($n = 1; $n <= 120; $n++) {
            $rows[] = [sprintf('NIV%03d', $n), "Niveau {$n}", $n, 'UNIV', 0];
        }

        DB::table('grade')->insertOrIgnore(array_map(fn ($r) => [
            'G_GRADE' => $r[0],
            'G_DESCRIPTION' => $r[1],
            'G_LEVEL' => $r[2],
            'G_TYPE' => 'tous',
            'G_CATEGORY' => $r[3],
            'G_ICON' => '',
            'G_FLAG' => $r[4] ?? 1,
        ], $rows));
    }

    public function down(): void
    {
        $codes = [
            'SAP2', 'SAP1', 'CPL', 'CCH', 'SGT', 'SCH', 'ADJ', 'ADC', 'LTN', 'CNE', 'CDT', 'LCL', 'COL', 'CHC', 'CGL', 'IGL',
            'ASD2', 'ASD1', 'ACPL', 'ACCH', 'ASGT', 'ASCH', 'AADJ', 'AADC', 'AMAJ', 'AASP', 'ASLT', 'ALTN', 'ACNE', 'ACDT', 'ALCL', 'ACOL', 'AGBR', 'AGDI', 'AGCA', 'AGAR',
            'PNGAR', 'PNBRC', 'PNMAJ', 'PNLTN', 'PNCNE', 'PNCDT', 'PNCDD', 'PNCOM', 'PNCDV', 'PNCGP', 'PNDGP',
        ];

        for ($n = 1; $n <= 120; $n++) {
            $codes[] = sprintf('NIV%03d', $n);
        }

        DB::table('grade')->whereIn('G_GRADE', $codes)->delete();
        DB::table('categorie_grade')->whereIn('CG_CODE', ['POL', 'UNIV'])->delete();
        DB::table('categorie_grade')->whereIn('CG_CODE', ['HOSP', 'PATS', 'SC', 'SSLIA'])->update(['CG_ACTIVE' => 1]);
    }
};
