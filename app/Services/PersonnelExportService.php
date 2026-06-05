<?php

namespace App\Services;

use App\Models\Personnel;
use Illuminate\Support\Facades\DB;
use Sabre\VObject\Component\VCard;
use FPDF;

/**
 * Handles per-member and list-level personnel exports:
 *  - VCard (.vcf)
 *  - PDF livret (passeport bénévole)
 *  - PDF carte adhérent
 *
 * PDF exports use FPDF (setasign/fpdf).
 * TODO: Add setasign/fpdi for letterhead template overlay once
 *       the package can be installed (see composer.json).
 */
class PersonnelExportService
{
    // ── VCard ─────────────────────────────────────────────────────────────────

    public function buildVcard(Personnel $personnel): string
    {
        $vcard = new VCard([
            'FN'  => trim(strtoupper($personnel->P_NOM) . ' ' . ucfirst(mb_strtolower($personnel->P_PRENOM))),
            'N'   => [strtoupper($personnel->P_NOM), ucfirst(mb_strtolower($personnel->P_PRENOM)), '', '', ''],
        ]);

        if ($personnel->P_EMAIL) {
            $vcard->add('EMAIL', $personnel->P_EMAIL, ['TYPE' => ['work', 'pref']]);
        }
        if ($personnel->P_PHONE) {
            $vcard->add('TEL', $personnel->P_PHONE, ['TYPE' => ['cell']]);
        }
        if ($personnel->P_PHONE2) {
            $vcard->add('TEL', $personnel->P_PHONE2, ['TYPE' => ['home']]);
        }
        if ($personnel->P_ADDRESS || $personnel->P_CITY) {
            $vcard->add('ADR', ['', '', $personnel->P_ADDRESS ?? '', $personnel->P_CITY ?? '', '', $personnel->P_ZIP_CODE ?? '', ''], ['TYPE' => ['home']]);
        }
        if ($personnel->P_BIRTHDATE) {
            $vcard->add('BDAY', $personnel->P_BIRTHDATE->format('Ymd'));
        }

        $section = $personnel->section;
        if ($section) {
            $vcard->add('ORG', [config('app.name'), $section->S_DESCRIPTION]);
        }

        return $vcard->serialize();
    }

    // ── PDF livret ────────────────────────────────────────────────────────────

    public function buildLivret(Personnel $personnel): string
    {
        $pid = (int) $personnel->P_ID;

        // ── Data loading ─────────────────────────────────────────────────────

        $medals = DB::table('agrement as a')
            ->join('type_agrement as ta', 'a.TA_CODE', '=', 'ta.TA_CODE')
            ->join('section as s2', 'a.S_ID', '=', 's2.S_ID')
            ->join('pompier as p', 'p.P_ID', '=', DB::raw($pid))
            ->join('section as s', 'p.P_SECTION', '=', 's.S_ID')
            ->where('ta.CA_CODE', '_MED')
            ->whereRaw('(a.S_ID = s.S_ID OR a.S_ID = s.S_PARENT)')
            ->select(
                'ta.TA_DESCRIPTION', 'a.A_COMMENT',
                DB::raw("DATE_FORMAT(a.A_DEBUT, '%d-%m-%Y') as A_DEBUT"),
                's2.S_DESCRIPTION'
            )
            ->get();

        $indivMedals = DB::table('qualification as q')
            ->join('poste as p', 'q.PS_ID', '=', 'p.PS_ID')
            ->join('equipe as e', 'p.EQ_ID', '=', 'e.EQ_ID')
            ->where('q.P_ID', $pid)
            ->where('e.EQ_NOM', 'Médailles et Récompenses')
            ->where(function ($qb) {
                $qb->whereNull('q.Q_EXPIRATION')->orWhere('q.Q_EXPIRATION', '>=', now());
            })
            ->orderBy('e.EQ_ID')
            ->select('p.TYPE', 'p.DESCRIPTION')
            ->get();

        $diplomes = DB::table('personnel_formation as pf')
            ->join('type_formation as tf', 'pf.TF_CODE', '=', 'tf.TF_CODE')
            ->join('poste as p', 'pf.PS_ID', '=', 'p.PS_ID')
            ->where('pf.P_ID', $pid)
            ->whereNotNull('pf.PF_DIPLOME')
            ->where('pf.PF_DIPLOME', '<>', '')
            ->select(
                'p.TYPE', 'p.DESCRIPTION',
                DB::raw("DATE_FORMAT(pf.PF_DATE, '%d-%m-%Y') as PF_DATE"),
                'pf.PF_DIPLOME', 'pf.PF_RESPONSABLE', 'pf.PF_LIEU'
            )
            ->get();

        $qualifications = DB::table('qualification as q')
            ->join('poste as p', 'q.PS_ID', '=', 'p.PS_ID')
            ->join('equipe as e', 'p.EQ_ID', '=', 'e.EQ_ID')
            ->where('q.P_ID', $pid)
            ->where('e.EQ_NOM', '<>', 'Médailles et Récompenses')
            ->where(function ($qb) {
                $qb->whereNull('q.Q_EXPIRATION')->orWhere('q.Q_EXPIRATION', '>=', now());
            })
            ->orderBy('e.EQ_ID')->orderBy('p.PH_CODE', 'desc')->orderBy('p.PH_LEVEL', 'desc')->orderBy('p.PS_ORDER')
            ->select(
                'e.EQ_NOM',
                'p.TYPE', 'p.DESCRIPTION',
                DB::raw("DATE_FORMAT(q.Q_EXPIRATION, '%d-%m-%Y') as Q_EXPIRATION")
            )
            ->get();

        $oneYearAgo = now()->subYear()->format('Y-m-d');
        $today      = now()->format('Y-m-d');

        $formations = $this->getActivities($pid, 'C_FOR', $oneYearAgo, $today);
        $secours    = $this->getActivities($pid, 'C_SEC', $oneYearAgo, $today);
        $operations = $this->getActivities($pid, 'C_OPE', $oneYearAgo, $today);

        $categories  = DB::table('categorie_evenement')->orderBy('CEV_CODE')->select('CEV_CODE', 'CEV_DESCRIPTION')->get();
        $currentYear = (int) date('Y');

        // ── PDF generation ───────────────────────────────────────────────────

        $pdf = new class extends FPDF {
            public $y = 50;      // phpcs:ignore -- no type: parent FPDF::$y is untyped
            public $goDown = 7;  // phpcs:ignore -- no type: same reason

            public function down(int $n = 1, int $ymax = 240): void
            {
                if ($this->y > $ymax) {
                    $this->AddPage();
                    $this->y = 50;
                } else {
                    $this->y += $n * $this->goDown;
                }
            }

            public function sectionHeader(string $text, int $imgX): void
            {
                $this->down(3, 220);
                $this->SetTextColor(0, 0, 0);
                $this->SetFont('Arial', 'B', 14);
                $this->SetXY(60, $this->y);
                $this->MultiCell(90, 8, $text, '1', 'C');
                $this->down(2);
            }
        };

        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();
        $pdf->AliasNbPages();

        // Title
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->SetXY(60, $pdf->y);
        $pdf->MultiCell(100, 15, mb_convert_encoding('Passeport du bénévole', 'ISO-8859-1', 'UTF-8'), '1', 'C');
        $pdf->down(4);

        // ── Identity ─────────────────────────────────────────────────────────

        $civMap = [1 => 'M.', 2 => 'Mme', 3 => 'Dr.', 4 => 'Pr.'];
        $civ    = $civMap[$personnel->P_CIVILITE] ?? '';

        // Photo
        $photoPath = '';
        foreach ([
            // TODO: Migrate code — trombi photos live in archive/legacy_app; move to storage/ after decommission
            base_path('archive/legacy_app/images/user-specific/trombi/' . $personnel->P_PHOTO),
            public_path('images/user-specific/trombi/' . $personnel->P_PHOTO),
        ] as $p) {
            if ($personnel->P_PHOTO && file_exists($p)) {
                $photoPath = $p;
                break;
            }
        }
        if ($photoPath) {
            $pdf->Image($photoPath, 15, $pdf->y + 5, 40);
        }

        $GoX  = 60;
        $GoX2 = 94;

        $nom    = mb_convert_encoding(strtoupper($personnel->P_NOM), 'ISO-8859-1', 'UTF-8');
        $prenom = mb_convert_encoding(ucfirst(mb_strtolower($personnel->P_PRENOM)), 'ISO-8859-1', 'UTF-8');

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Text($GoX, $pdf->y, mb_convert_encoding('Identité: ', 'ISO-8859-1', 'UTF-8'));
        $pdf->SetTextColor(13, 53, 148);
        $pdf->Text($GoX2, $pdf->y, mb_convert_encoding($civ . ' ' . $nom . ' ' . $prenom, 'ISO-8859-1', 'UTF-8'));
        $pdf->SetTextColor(0, 0, 0);
        $pdf->down();

        $pdf->SetFont('Arial', '', 10);

        $fields = [
            'Date de naissance:'  => $personnel->P_BIRTHDATE?->format('d-m-Y') ?? '',
            'Lieu de naissance:'  => ($personnel->P_BIRTHPLACE ?? '') . ($personnel->P_BIRTH_DEP ? ' (' . $personnel->P_BIRTH_DEP . ')' : ''),
            'Adresse:'            => $personnel->P_ADDRESS ?? '',
            ''                    => trim(($personnel->P_ZIP_CODE ?? '') . ' ' . ($personnel->P_CITY ?? '')),
            'Téléphone:'          => $personnel->P_PHONE ?? '',
            'Email:'              => $personnel->P_EMAIL ?? '',
            'Date engagement:'    => $personnel->P_DATE_ENGAGEMENT?->format('d-m-Y') ?? '',
        ];

        if ($personnel->section) {
            $fields['Section:'] = mb_convert_encoding($personnel->section->S_DESCRIPTION, 'ISO-8859-1', 'UTF-8');
        }

        foreach ($fields as $label => $value) {
            if ($label) {
                $pdf->Text($GoX, $pdf->y, mb_convert_encoding($label, 'ISO-8859-1', 'UTF-8'));
            }
            $pdf->Text($GoX2, $pdf->y, mb_convert_encoding((string) $value, 'ISO-8859-1', 'UTF-8'));
            $pdf->down();
        }

        // ── Collective medals ─────────────────────────────────────────────────

        if ($medals->isNotEmpty()) {
            $pdf->sectionHeader(mb_convert_encoding('Décorations collectives', 'ISO-8859-1', 'UTF-8'), 50);

            $h = 8; $sx = 15;
            $L1 = 60; $L2 = 25; $L3 = 54; $L4 = 45;

            $pdf->SetXY($sx, $pdf->y);
            $pdf->SetFillColor(200, 200, 200);
            $pdf->SetFont('Arial', 'B', 11);
            foreach ([[$L1, 'Médaille'], [$L2, 'Date'], [$L3, 'Agrafe'], [$L4, 'Décernée à']] as [$w, $t]) {
                $pdf->MultiCell($w, $h, mb_convert_encoding($t, 'ISO-8859-1', 'UTF-8'), 1, 'C', true);
                $pdf->SetXY($pdf->GetX() + ($pdf->GetX() === $sx ? 0 : 0), $pdf->y);
            }
            // reset to start of next row
            $pdf->SetXY($sx, $pdf->y + $h);
            $pdf->y = (int) ($pdf->y + $h);

            foreach ($medals as $m) {
                $pdf->SetXY($sx, $pdf->y);
                $pdf->SetFont('Arial', '', 8);
                $pdf->SetFillColor(255, 255, 255);
                $pdf->MultiCell($L1, $h, mb_convert_encoding(substr($m->TA_DESCRIPTION, 0, 40), 'ISO-8859-1', 'UTF-8'), 1, 'C', true);
                $pdf->SetXY($sx + $L1, $pdf->y);
                $pdf->MultiCell($L2, $h, mb_convert_encoding($m->A_DEBUT ?? '', 'ISO-8859-1', 'UTF-8'), 1, 'C', true);
                $pdf->SetXY($sx + $L1 + $L2, $pdf->y);
                $pdf->MultiCell($L3, $h, mb_convert_encoding(substr($m->A_COMMENT ?? '', 0, 35), 'ISO-8859-1', 'UTF-8'), 1, 'C', true);
                $pdf->SetXY($sx + $L1 + $L2 + $L3, $pdf->y);
                $pdf->MultiCell($L4, $h, mb_convert_encoding(substr($m->S_DESCRIPTION, 0, 28), 'ISO-8859-1', 'UTF-8'), 1, 'C', true);
                $pdf->y = (int) ($pdf->y + $h);
                if ($pdf->y > 265) {
                    $pdf->AddPage();
                    $pdf->y = 50;
                }
            }
        }

        // ── Individual medals ─────────────────────────────────────────────────

        if ($indivMedals->isNotEmpty()) {
            $pdf->sectionHeader(mb_convert_encoding('Médailles et Récompenses', 'ISO-8859-1', 'UTF-8'), 50);

            $h = 8; $sx = 15;
            $L1 = 110; $L2 = 30; $L3 = 44;

            $pdf->SetXY($sx, $pdf->y);
            $pdf->SetFillColor(200, 200, 200);
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->MultiCell($L1, $h, mb_convert_encoding('Médaille', 'ISO-8859-1', 'UTF-8'), 1, 'C', true);
            $pdf->SetXY($sx + $L1, $pdf->y);
            $pdf->MultiCell($L2, $h, mb_convert_encoding('Décernée', 'ISO-8859-1', 'UTF-8'), 1, 'C', true);
            $pdf->SetXY($sx + $L1 + $L2, $pdf->y);
            $pdf->MultiCell($L3, $h, 'à', 1, 'C', true);
            $pdf->y = (int) ($pdf->y + $h);

            foreach ($indivMedals as $m) {
                $pdf->SetXY($sx, $pdf->y);
                $pdf->SetFont('Arial', '', 8);
                $pdf->SetFillColor(255, 255, 255);
                $pdf->MultiCell($L1, $h, mb_convert_encoding(substr($m->DESCRIPTION, 0, 65), 'ISO-8859-1', 'UTF-8'), 1, 'C', true);
                $pdf->SetXY($sx + $L1, $pdf->y);
                $pdf->MultiCell($L2, $h, mb_convert_encoding('à titre individuel', 'ISO-8859-1', 'UTF-8'), 1, 'C', true);
                $pdf->SetXY($sx + $L1 + $L2, $pdf->y);
                $pdf->MultiCell($L3, $h, mb_convert_encoding($prenom . ' ' . $nom, 'ISO-8859-1', 'UTF-8'), 1, 'C', true);
                $pdf->y = (int) ($pdf->y + $h);
                if ($pdf->y > 265) {
                    $pdf->AddPage();
                    $pdf->y = 50;
                }
            }
        }

        // ── Diplomes ─────────────────────────────────────────────────────────

        if ($diplomes->isNotEmpty()) {
            $pdf->sectionHeader(mb_convert_encoding('Diplômes officiels', 'ISO-8859-1', 'UTF-8'), 50);

            $h = 8; $sx = 15;
            $L1 = 16; $L2 = 50; $L3 = 20; $L4 = 28; $L5 = 42; $L6 = 28;

            $pdf->SetXY($sx, $pdf->y);
            $pdf->SetFillColor(200, 200, 200);
            $pdf->SetFont('Arial', 'B', 11);
            foreach ([[$L1, 'Code'], [$L2, 'Qualification'], [$L3, 'Date'], [$L4, 'Num diplôme'], [$L5, 'Délivré par'], [$L6, 'Lieu']] as $i => [$w, $t]) {
                $x = $sx + array_sum(array_column(array_slice([[$L1], [$L2], [$L3], [$L4], [$L5], [$L6]], 0, $i), 0));
                $pdf->SetXY($x, $pdf->y);
                $pdf->MultiCell($w, $h, mb_convert_encoding($t, 'ISO-8859-1', 'UTF-8'), 1, 'C', true);
            }
            $pdf->y = (int) ($pdf->y + $h);

            foreach ($diplomes as $d) {
                $pdf->SetXY($sx, $pdf->y);
                $pdf->SetFont('Arial', '', 8);
                $pdf->SetFillColor(200, 200, 200);
                $pdf->MultiCell($L1, $h, mb_convert_encoding(substr($d->TYPE, 0, 8), 'ISO-8859-1', 'UTF-8'), 1, 'L', true);
                $pdf->SetFillColor(255, 255, 255);
                $pdf->SetXY($sx + $L1, $pdf->y);
                $pdf->MultiCell($L2, $h, mb_convert_encoding(substr($d->DESCRIPTION, 0, 30), 'ISO-8859-1', 'UTF-8'), 1, 'C', true);
                $pdf->SetXY($sx + $L1 + $L2, $pdf->y);
                $pdf->MultiCell($L3, $h, mb_convert_encoding($d->PF_DATE ?? '', 'ISO-8859-1', 'UTF-8'), 1, 'C', true);
                $pdf->SetXY($sx + $L1 + $L2 + $L3, $pdf->y);
                $pdf->MultiCell($L4, $h, mb_convert_encoding(substr($d->PF_DIPLOME ?? '', 0, 18), 'ISO-8859-1', 'UTF-8'), 1, 'C', true);
                $pdf->SetXY($sx + $L1 + $L2 + $L3 + $L4, $pdf->y);
                $pdf->MultiCell($L5, $h, mb_convert_encoding(substr($d->PF_RESPONSABLE ?? '', 0, 23), 'ISO-8859-1', 'UTF-8'), 1, 'C', true);
                $pdf->SetXY($sx + $L1 + $L2 + $L3 + $L4 + $L5, $pdf->y);
                $pdf->MultiCell($L6, $h, mb_convert_encoding(substr($d->PF_LIEU ?? '', 0, 18), 'ISO-8859-1', 'UTF-8'), 1, 'C', true);
                $pdf->y = (int) ($pdf->y + $h);
                if ($pdf->y > 265) {
                    $pdf->AddPage();
                    $pdf->y = 50;
                }
            }
        }

        // ── Qualifications ────────────────────────────────────────────────────

        if ($qualifications->isNotEmpty()) {
            $pdf->sectionHeader(mb_convert_encoding('Compétences valides au ' . date('d-m-Y'), 'ISO-8859-1', 'UTF-8'), 40);

            $h = 7; $sx = 15;
            $L1 = 45; $L2 = 25; $L3 = 80; $L4 = 30;

            $pdf->SetXY($sx, $pdf->y);
            $pdf->SetFillColor(200, 200, 200);
            $pdf->SetFont('Arial', 'B', 9);
            foreach ([[$L1, 'Catégorie'], [$L2, 'Type'], [$L3, 'Description'], [$L4, 'Expiration']] as $i => [$w, $t]) {
                $x = $sx + array_sum(array_column(array_slice([[$L1], [$L2], [$L3], [$L4]], 0, $i), 0));
                $pdf->SetXY($x, $pdf->y);
                $pdf->MultiCell($w, $h, mb_convert_encoding($t, 'ISO-8859-1', 'UTF-8'), 1, 'C', true);
            }
            $pdf->y = (int) ($pdf->y + $h);

            $prevEq = '';
            foreach ($qualifications as $q) {
                $cat = ($q->EQ_NOM !== $prevEq) ? $q->EQ_NOM : '';
                $prevEq = $q->EQ_NOM;

                $pdf->SetXY($sx, $pdf->y);
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->SetFillColor(200, 200, 200);
                $pdf->MultiCell($L1, $h, mb_convert_encoding(substr($cat, 0, 28), 'ISO-8859-1', 'UTF-8'), 1, 'L', true);
                $pdf->SetFont('Arial', '', 8);
                $pdf->SetFillColor(255, 255, 255);
                $pdf->SetXY($sx + $L1, $pdf->y);
                $pdf->MultiCell($L2, $h, mb_convert_encoding(substr($q->TYPE, 0, 14), 'ISO-8859-1', 'UTF-8'), 1, 'C', true);
                $pdf->SetXY($sx + $L1 + $L2, $pdf->y);
                $pdf->MultiCell($L3, $h, mb_convert_encoding(substr($q->DESCRIPTION, 0, 50), 'ISO-8859-1', 'UTF-8'), 1, 'C', true);
                $pdf->SetXY($sx + $L1 + $L2 + $L3, $pdf->y);
                $pdf->MultiCell($L4, $h, mb_convert_encoding($q->Q_EXPIRATION ?? '', 'ISO-8859-1', 'UTF-8'), 1, 'C', true);
                $pdf->y = (int) ($pdf->y + $h);
                if ($pdf->y > 265) {
                    $pdf->AddPage();
                    $pdf->y = 50;
                }
            }
        }

        // ── Formations ────────────────────────────────────────────────────────

        if ($formations->isNotEmpty()) {
            $pdf->sectionHeader(mb_convert_encoding('Formations depuis 1 an', 'ISO-8859-1', 'UTF-8'), 50);
            $this->renderActivityTable($pdf, $formations, 'formation');
        }

        // ── Secours ───────────────────────────────────────────────────────────

        if ($secours->isNotEmpty()) {
            $pdf->sectionHeader(mb_convert_encoding('Opérations de secours depuis 1 an', 'ISO-8859-1', 'UTF-8'), 50);
            $this->renderActivityTable($pdf, $secours, 'secours');
        }

        // ── Opérations ────────────────────────────────────────────────────────

        if ($operations->isNotEmpty()) {
            $pdf->sectionHeader(mb_convert_encoding('Activités opérationnelles depuis 1 an', 'ISO-8859-1', 'UTF-8'), 50);
            $this->renderActivityTable($pdf, $operations, 'operation');
        }

        // ── 5-year summary ────────────────────────────────────────────────────

        $pdf->sectionHeader(mb_convert_encoding('Bilan participations bénévole sur 5 ans', 'ISO-8859-1', 'UTF-8'), 40);

        $h = 8; $sx = 15; $Y = $currentYear;
        $L1 = 48; $Lw = 25;

        $pdf->SetXY($sx, $pdf->y);
        $pdf->SetFillColor(200, 200, 200);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->MultiCell($L1, $h, mb_convert_encoding('Activité', 'ISO-8859-1', 'UTF-8'), 1, 'C', true);
        for ($i = 4; $i >= 0; $i--) {
            $pdf->SetXY($sx + $L1 + (4 - $i) * $Lw, $pdf->y);
            $pdf->MultiCell($Lw, $h, (string) ($Y - $i), 1, 'C', true);
        }
        $pdf->y = (int) ($pdf->y + $h);

        foreach ($categories as $cat) {
            $pdf->SetXY($sx, $pdf->y);
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetFillColor(200, 200, 200);
            $pdf->MultiCell($L1, $h, mb_convert_encoding(ucfirst(mb_strtolower($cat->CEV_DESCRIPTION)), 'ISO-8859-1', 'UTF-8'), 1, 'L', true);
            $pdf->SetFillColor(255, 255, 255);
            for ($i = 4; $i >= 0; $i--) {
                $nb = $this->getHoursForYear($pid, $cat->CEV_CODE, $Y - $i);
                $pdf->SetXY($sx + $L1 + (4 - $i) * $Lw, $pdf->y);
                $pdf->MultiCell($Lw, $h, (string) $nb, 1, 'C', true);
            }
            $pdf->y = (int) ($pdf->y + $h);
            if ($pdf->y > 265) {
                $pdf->AddPage();
                $pdf->y = 50;
            }
        }

        // ── Footer ────────────────────────────────────────────────────────────

        $pdf->SetXY(10, 272);
        $pdf->SetFont('Arial', '', 6);
        $printedBy = mb_convert_encoding('Imprimé le ' . now()->format('d-m-Y à H:i'), 'ISO-8859-1', 'UTF-8');
        $pdf->MultiCell(100, 5, $printedBy, '', 'L');

        return $pdf->Output('S');
    }

    // ── PDF carte adhérent ────────────────────────────────────────────────────

    public function buildCarte(Personnel $personnel): string
    {
        $pdf = new FPDF('L', 'mm', [85.6, 53.98]); // credit-card landscape
        $pdf->SetMargins(0, 0);
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();

        // Background
        $pdf->SetFillColor(220, 230, 245);
        $pdf->Rect(0, 0, 85.6, 53.98, 'F');

        // Header bar
        $pdf->SetFillColor(30, 60, 120);
        $pdf->Rect(0, 0, 85.6, 12, 'F');
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetXY(2, 3);
        $pdf->Cell(81.6, 6, mb_convert_encoding(config('app.name', 'OpenBrigade'), 'ISO-8859-1', 'UTF-8'), 0, 0, 'C');

        // Photo
        $photoPath = '';
        foreach ([
            // TODO: Migrate code — trombi photos live in archive/legacy_app; move to storage/ after decommission
            base_path('archive/legacy_app/images/user-specific/trombi/' . $personnel->P_PHOTO),
            public_path('images/user-specific/trombi/' . $personnel->P_PHOTO),
        ] as $p) {
            if ($personnel->P_PHOTO && file_exists($p)) {
                $photoPath = $p;
                break;
            }
        }
        if ($photoPath) {
            $pdf->Image($photoPath, 3, 14, 20, 26);
        }

        // Member info
        $pdf->SetTextColor(0, 0, 60);
        $pdf->SetFont('Arial', 'B', 9);
        $nom    = mb_convert_encoding(strtoupper($personnel->P_NOM), 'ISO-8859-1', 'UTF-8');
        $prenom = mb_convert_encoding(ucfirst(mb_strtolower($personnel->P_PRENOM)), 'ISO-8859-1', 'UTF-8');

        $pdf->SetXY(26, 15);
        $pdf->Cell(57, 6, $nom, 0, 1, 'L');
        $pdf->SetXY(26, 21);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(57, 6, $prenom, 0, 1, 'L');

        if ($personnel->P_GRADE) {
            $pdf->SetXY(26, 27);
            $pdf->SetFont('Arial', 'I', 8);
            $pdf->Cell(57, 5, mb_convert_encoding($personnel->P_GRADE, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        }

        $pdf->SetXY(26, 33);
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->Cell(57, 5, mb_convert_encoding('N° ' . $personnel->P_CODE, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');

        if ($personnel->section) {
            $pdf->SetXY(26, 38);
            $pdf->Cell(57, 5, mb_convert_encoding(substr($personnel->section->S_DESCRIPTION, 0, 32), 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        }

        // Footer
        $pdf->SetFillColor(30, 60, 120);
        $pdf->Rect(0, 46.98, 85.6, 7, 'F');
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'I', 6);
        $pdf->SetXY(2, 48);
        $pdf->Cell(81.6, 4, mb_convert_encoding('Carte de membre — ' . date('Y'), 'ISO-8859-1', 'UTF-8'), 0, 0, 'C');

        return $pdf->Output('S');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function getActivities(int $pid, string $cevCode, string $from, string $to)
    {
        return DB::table('evenement_participation as ep')
            ->join('evenement_horaire as eh', 'ep.EH_ID', '=', 'eh.EH_ID')
            ->join('evenement as e', 'e.E_CODE', '=', 'ep.E_CODE')
            ->join('type_evenement as te', 'e.TE_CODE', '=', 'te.TE_CODE')
            ->leftJoin('type_participation as tp', 'ep.TP_ID', '=', 'tp.TP_ID')
            ->leftJoin('type_formation as tf', 'e.TF_CODE', '=', 'tf.TF_CODE')
            ->where('ep.P_ID', $pid)
            ->where('te.CEV_CODE', $cevCode)
            ->where('e.E_CANCELED', 0)
            ->where('ep.EP_ABSENT', 0)
            ->where('ep.EP_FLAG1', 0)
            ->where('e.E_VISIBLE_INSIDE', 1)
            ->whereRaw("DATE_FORMAT(eh.EH_DATE_FIN, '%Y-%m-%d') BETWEEN ? AND ?", [$from, $to])
            ->orderBy('eh.EH_DATE_DEBUT', 'desc')
            ->select(
                'e.E_LIBELLE', 'e.E_LIEU', 'te.TE_LIBELLE', 'te.TE_CODE',
                DB::raw("DATE_FORMAT(COALESCE(ep.EP_DATE_DEBUT, eh.EH_DATE_DEBUT), '%d-%m-%Y') as datedeb"),
                'eh.EH_DUREE', 'ep.EP_DUREE',
                'tp.TP_LIBELLE', 'tf.TF_LIBELLE', 'tf.TF_CODE'
            )
            ->get();
    }

    private function getHoursForYear(int $pid, string $cevCode, int $year): int
    {
        $rows = DB::table('evenement_participation as ep')
            ->join('evenement_horaire as eh', 'ep.EH_ID', '=', 'eh.EH_ID')
            ->join('evenement as e', 'e.E_CODE', '=', 'ep.E_CODE')
            ->join('type_evenement as te', 'e.TE_CODE', '=', 'te.TE_CODE')
            ->where('ep.P_ID', $pid)
            ->where('te.CEV_CODE', $cevCode)
            ->where('e.E_CANCELED', 0)
            ->where('ep.EP_ABSENT', 0)
            ->where('ep.EP_FLAG1', 0)
            ->where('e.E_VISIBLE_INSIDE', 1)
            ->where('te.TE_CODE', '<>', 'MC')
            ->whereRaw("YEAR(eh.EH_DATE_FIN) = ?", [$year])
            ->select('eh.EH_DUREE', 'ep.EP_DUREE')
            ->get();

        $total = 0;
        foreach ($rows as $r) {
            $total += ($r->EP_DUREE > 0) ? $r->EP_DUREE : $r->EH_DUREE;
        }
        return (int) round($total);
    }

    private function renderActivityTable($pdf, $activities, string $type): void
    {
        $h = 8; $sx = 15;
        $sum = 0;

        if ($type === 'formation') {
            $L1 = 18; $L2 = 28; $L3 = 16; $L4 = 46; $L5 = 29; $L6 = 16; $L7 = 30;
            $cols = [[$L1, 'Date'], [$L2, 'Type'], [$L3, 'Pour'], [$L4, 'Description'], [$L5, 'Lieu'], [$L6, 'Heures'], [$L7, 'Rôle']];
        } else {
            $L1 = 18; $L2 = 30; $L3 = 50; $L4 = 36; $L5 = 16; $L6 = 30;
            $cols = [[$L1, 'Date'], [$L2, 'Activité'], [$L3, 'Description'], [$L4, 'Lieu'], [$L5, 'Heures'], [$L6, 'Rôle']];
        }

        $widths = array_column($cols, 0);
        $pdf->SetXY($sx, $pdf->y);
        $pdf->SetFillColor(200, 200, 200);
        $pdf->SetFont('Arial', 'B', 11);
        foreach ($cols as $i => [$w, $t]) {
            $x = $sx + array_sum(array_slice($widths, 0, $i));
            $pdf->SetXY($x, $pdf->y);
            $pdf->MultiCell($w, $h, mb_convert_encoding($t, 'ISO-8859-1', 'UTF-8'), 1, 'C', true);
        }
        $pdf->y = (int) ($pdf->y + $h);

        foreach ($activities as $a) {
            $duree  = ($a->EP_DUREE > 0) ? $a->EP_DUREE : $a->EH_DUREE;
            $sum   += (int) $duree;
            $role   = substr($a->TP_LIBELLE ?? 'stagiaire', 0, 20);
            $lieu   = substr($a->E_LIEU ?? '', 0, 20);

            if ($type === 'formation') {
                $tf   = ($a->TF_CODE === 'I') ? 'Formation initiale' : substr($a->TF_LIBELLE ?? '', 0, 18);
                $pour = substr($a->TE_CODE ?? '', 0, 8);
                $desc = substr($a->E_LIBELLE ?? '', 0, 28);
                $rowData = [$a->datedeb, $tf, $pour, $desc, $lieu, (string) $duree, $role];
            } else {
                $te   = ($a->TE_CODE === 'DPS') ? 'DPS' : substr($a->TE_LIBELLE ?? '', 0, 20);
                $desc = substr($a->E_LIBELLE ?? '', 0, 30);
                $rowData = [$a->datedeb, $te, $desc, $lieu, (string) $duree, $role];
            }

            $pdf->SetXY($sx, $pdf->y);
            $pdf->SetFont('Arial', '', 8);
            foreach ($rowData as $i => $val) {
                $x = $sx + array_sum(array_slice($widths, 0, $i));
                $pdf->SetXY($x, $pdf->y);
                $fill = ($i === 0);
                $pdf->SetFillColor($fill ? 200 : 255, $fill ? 200 : 255, $fill ? 200 : 255);
                $pdf->MultiCell($widths[$i], $h, mb_convert_encoding((string) $val, 'ISO-8859-1', 'UTF-8'), 1, 'C', true);
            }
            $pdf->y = (int) ($pdf->y + $h);
            if ($pdf->y > 265) {
                $pdf->AddPage();
                $pdf->y = 50;
            }
        }

        $pdf->down(2);
        $label = match($type) {
            'formation' => "Nombre total d'heures de formation bénévole depuis un an: {$sum}h",
            'secours'   => "Nombre total d'heures de participation aux activités de secours depuis un an: {$sum}h",
            default     => "Nombre total d'heures de participation aux activités opérationnelles depuis un an: {$sum}h",
        };
        $pdf->SetFont('Arial', '', 10);
        $pdf->Text(15, $pdf->y, mb_convert_encoding($label, 'ISO-8859-1', 'UTF-8'));
    }
}
