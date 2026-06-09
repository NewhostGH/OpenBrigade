<?php

namespace App\Services;

use Carbon\Carbon;
use FPDF;

/**
 * Generates bilan annuel PDFs using FPDF.
 *
 * Three public entry points correspond to the three bilan tabs:
 *   buildGeneralites(), buildActivites(), buildFormations()
 * Each receives the same data array returned by the controller's fetch* method
 * and returns a raw PDF string suitable for streaming.
 */
class BilanPdfService
{
    // Brand colours
    private const BRAND  = [43,  35,  80];   // #2B2350
    private const ACCENT = [250, 112, 112];   // #FA7070
    private const LIGHT  = [245, 245, 250];
    private const GREY   = [130, 130, 130];
    private const WHITE  = [255, 255, 255];
    private const BLACK  = [30,  30,  30];

    private const PAGE_W  = 186;  // usable width (A4 210 − 2×12 margins)
    private const MARGIN  = 12;

    // ── Public API ─────────────────────────────────────────────────────────────

    public function buildGeneralites(array $d): string
    {
        $pdf = $this->makePdf($d, 'Généralités - Personnel & Moyens');

        // ── Personnel ──────────────────────────────────────────────────────
        $this->sectionTitle($pdf, 'A - Personnel');
        $this->kpiRow($pdf, [
            ['Membres actifs',                        (string) $d['totalMembers']],
            ['Nouveaux '.(now()->year - 1),           (string) ($d['newMembersByYear'][now()->year - 1] ?? 0)],
        ]);

        if (! empty($d['membersByGroup'])) {
            $this->subTitle($pdf, 'Répartition par groupe');
            $this->table($pdf,
                ['Groupe', 'Effectif'],
                array_map(fn ($l, $n) => [$l, $n], array_keys($d['membersByGroup']), array_values($d['membersByGroup'])),
                [150, 36]
            );
        }

        if (! empty($d['newMembersByYear'])) {
            $this->subTitle($pdf, 'Évolution des engagements (5 ans)');
            $this->table($pdf,
                ['Année', 'Nouveaux membres'],
                array_map(fn ($y, $n) => [(string) $y, $n], array_keys($d['newMembersByYear']), array_values($d['newMembersByYear'])),
                [100, 86]
            );
        }

        // ── Véhicules ──────────────────────────────────────────────────────
        $this->sectionTitle($pdf, 'B - Véhicules');
        $this->kpiRow($pdf, [
            ['Total véhicules', (string) $d['totalVehicles']],
        ]);

        if ($d['vehiclesByType']->isNotEmpty()) {
            $this->table($pdf,
                ['Type de véhicule', 'Quantité'],
                $d['vehiclesByType']->map(fn ($r) => [$r->label, (string) $r->nb])->toArray(),
                [150, 36]
            );
        }

        // ── Matériel ───────────────────────────────────────────────────────
        $this->sectionTitle($pdf, 'C - Matériel');
        $this->kpiRow($pdf, [
            ['Total matériel', (string) $d['totalMateriels']],
        ]);

        if ($d['materielsByType']->isNotEmpty()) {
            $this->table($pdf,
                ['Catégorie', 'Quantité'],
                $d['materielsByType']->map(fn ($r) => [$r->label, (string) $r->nb])->toArray(),
                [150, 36]
            );
        }

        // ── Consommables ───────────────────────────────────────────────────
        $this->sectionTitle($pdf, 'D - Consommables');
        $this->kpiRow($pdf, [
            ['Total consommables', (string) $d['totalConsommables']],
        ]);

        if ($d['consommablesByType']->isNotEmpty()) {
            $this->table($pdf,
                ['Catégorie', 'Quantité'],
                $d['consommablesByType']->map(fn ($r) => [$r->label, (string) $r->nb])->toArray(),
                [150, 36]
            );
        }

        return $pdf->Output('S');
    }

    public function buildActivites(array $d): string
    {
        $pdf = $this->makePdf($d, 'Activités opérationnelles');

        $months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];

        // ── Synthèse ───────────────────────────────────────────────────────
        $this->sectionTitle($pdf, 'A - Synthèse');
        $this->kpiRow($pdf, [
            ['Activités',            (string) $d['totalEvents']],
            ['Participations',        (string) $d['totalParticipants']],
            ['Heures bénévoles',      number_format($d['totalHours'], 0, ',', ' ')],
        ]);

        // ── Répartition mensuelle ──────────────────────────────────────────
        $this->sectionTitle($pdf, 'B - Répartition mensuelle');
        $rows = [];
        foreach ($months as $i => $m) {
            $rows[] = [$m, (string) ($d['eventsData'][$i] ?? 0), (string) ($d['participantData'][$i] ?? 0)];
        }
        $this->table($pdf, ['Mois', 'Activités', 'Participants'], $rows, [80, 53, 53]);

        // ── Répartition par type ───────────────────────────────────────────
        if (! empty($d['eventsByType'])) {
            $this->sectionTitle($pdf, 'C - Répartition par type');
            $this->table($pdf,
                ["Type d'activité", 'Nombre'],
                array_map(fn ($l, $n) => [$l, (string) $n], array_keys($d['eventsByType']), array_values($d['eventsByType'])),
                [150, 36]
            );
        }

        // ── Top participants ───────────────────────────────────────────────
        if ($d['topParticipants']->isNotEmpty()) {
            $this->sectionTitle($pdf, 'D - Top 10 participants');
            $rows = [];
            foreach ($d['topParticipants'] as $i => $p) {
                $rows[] = [(string) ($i + 1), $p->P_PRENOM.' '.strtoupper($p->P_NOM), (string) $p->nb_events];
            }
            $this->table($pdf, ['#', 'Membre', 'Activités'], $rows, [16, 134, 36]);
        }

        return $pdf->Output('S');
    }

    public function buildFormations(array $d): string
    {
        $pdf = $this->makePdf($d, 'Formations');

        $months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];

        // ── Synthèse ───────────────────────────────────────────────────────
        $this->sectionTitle($pdf, 'A - Synthèse');
        $this->kpiRow($pdf, [
            ['Sessions de formation',  (string) $d['totalFormations']],
            ['Stagiaires (cumulés)',   (string) $d['totalTrained']],
            ['Heures dispensées',      number_format($d['totalHours'], 0, ',', ' ')],
        ]);

        // ── Répartition mensuelle ──────────────────────────────────────────
        $this->sectionTitle($pdf, 'B - Répartition mensuelle');
        $rows = [];
        foreach ($months as $i => $m) {
            $rows[] = [$m, (string) ($d['eventsData'][$i] ?? 0)];
        }
        $this->table($pdf, ['Mois', 'Sessions'], $rows, [130, 56]);

        // ── Répartition par type ───────────────────────────────────────────
        if (! empty($d['eventsByType'])) {
            $this->sectionTitle($pdf, 'C - Répartition par type');
            $this->table($pdf,
                ['Type de formation', 'Nombre'],
                array_map(fn ($l, $n) => [$l, (string) $n], array_keys($d['eventsByType']), array_values($d['eventsByType'])),
                [150, 36]
            );
        }

        // ── Liste des sessions ─────────────────────────────────────────────
        if ($d['formationsList']->isNotEmpty()) {
            $this->sectionTitle($pdf, 'D - Liste des sessions');
            $rows = [];
            foreach ($d['formationsList'] as $f) {
                $rows[] = [
                    Carbon::parse($f->date)->format('d/m/Y'),
                    $f->label ?: '-',
                    $f->lieu  ?: '-',
                    (string) ($f->duree_h ?: '-'),
                    (string) $f->nb_participants,
                ];
            }
            $this->table($pdf, ['Date', 'Intitulé', 'Lieu', 'h', 'Eff.'], $rows, [24, 74, 50, 14, 24]);

            // Totals
            $this->totalsRow($pdf,
                ['', 'TOTAL', '', (string) $d['totalHours'], (string) $d['totalTrained']],
                [24, 74, 50, 14, 24]
            );
        }

        return $pdf->Output('S');
    }

    // ── PDF factory ───────────────────────────────────────────────────────────

    private function makePdf(array $d, string $subtitle): object
    {
        $section = $d['section'] ?? null;
        $orgName = $section ? ($section->S_DESCRIPTION ?? config('app.name')) : config('app.name');
        $year    = (int) $d['year'];

        $pdf = new class extends FPDF
        {
            public string $orgName  = '';
            public string $subtitle = '';
            public int    $year     = 0;

            public function Header(): void
            {
                // Brand bar
                $this->SetFillColor(43, 35, 80);
                $this->Rect(0, 0, 210, 20, 'F');

                // Org name
                $this->SetTextColor(255, 255, 255);
                $this->SetFont('Arial', 'B', 12);
                $this->SetXY(12, 4);
                $this->Cell(120, 6, $this->e($this->orgName), 0, 0, 'L');

                // Year label
                $this->SetFont('Arial', 'B', 11);
                $this->SetXY(134, 4);
                $this->Cell(64, 6, $this->e('BILAN '.$this->year), 0, 0, 'R');

                // Subtitle
                $this->SetFont('Arial', '', 8);
                $this->SetXY(134, 11);
                $this->Cell(64, 5, $this->e($this->subtitle), 0, 0, 'R');

                $this->SetTextColor(30, 30, 30);
                $this->SetY(24);
            }

            public function Footer(): void
            {
                $this->SetY(-13);
                $this->SetDrawColor(200, 200, 200);
                $this->Line(12, $this->GetY(), 198, $this->GetY());
                $this->Ln(1);
                $this->SetFont('Arial', 'I', 7);
                $this->SetTextColor(150, 150, 150);
                $generated = $this->e('Généré avec OpenBrigade le '.now()->format('d/m/Y à H:i'));
                $this->Cell(130, 5, $generated, 0, 0, 'L');
                $this->Cell(0, 5, $this->e('Page '.$this->PageNo().'/{nb}'), 0, 0, 'R');
            }

            public function e(string $s): string
            {
                return iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $s) ?: '';
            }
        };

        $pdf->orgName  = $orgName;
        $pdf->subtitle = $subtitle;
        $pdf->year     = $year;

        $pdf->SetMargins(self::MARGIN, self::MARGIN, self::MARGIN);
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->AliasNbPages();
        $pdf->AddPage();

        // Cover title block
        $pdf->SetFillColor(...self::LIGHT);
        $pdf->Rect(self::MARGIN, $pdf->GetY(), self::PAGE_W, 22, 'F');
        $pdf->SetFillColor(...self::BRAND);
        $pdf->Rect(self::MARGIN, $pdf->GetY(), 3, 22, 'F');

        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor(...self::BRAND);
        $pdf->SetXY(self::MARGIN + 6, $pdf->GetY() + 4);
        $pdf->Cell(self::PAGE_W - 6, 8, $pdf->e('BILAN '.$year.' - '.$orgName), 0, 1, 'L');

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(...self::GREY);
        $pdf->SetX(self::MARGIN + 6);
        $pdf->Cell(self::PAGE_W - 6, 6, $pdf->e($subtitle), 0, 1, 'L');

        $pdf->SetTextColor(...self::BLACK);
        $pdf->Ln(8);

        return $pdf;
    }

    // ── Layout helpers ────────────────────────────────────────────────────────

    private function enc(string $s): string
    {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $s) ?: '';
    }

    private function sectionTitle(object $pdf, string $title): void
    {
        if ($pdf->GetY() > 248) {
            $pdf->AddPage();
        }
        $pdf->Ln(4);
        $pdf->SetFillColor(...self::BRAND);
        $pdf->SetTextColor(...self::WHITE);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetX(self::MARGIN);
        $pdf->Cell(self::PAGE_W, 7, $this->enc($title), 0, 1, 'L', true);
        $pdf->SetTextColor(...self::BLACK);
        $pdf->Ln(3);
    }

    private function subTitle(object $pdf, string $title): void
    {
        $pdf->Ln(3);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(...self::GREY);
        $pdf->SetX(self::MARGIN);
        $pdf->Cell(self::PAGE_W, 5, $this->enc(mb_strtoupper($title, 'UTF-8')), 0, 1, 'L');
        $pdf->SetTextColor(...self::BLACK);
        $pdf->Ln(1);
    }

    /** @param array<array{0:string,1:string}> $kpis */
    private function kpiRow(object $pdf, array $kpis): void
    {
        $n    = count($kpis);
        $colW = (int) floor(self::PAGE_W / $n);
        $y    = $pdf->GetY();

        foreach ($kpis as $i => [$label, $value]) {
            $x = self::MARGIN + $i * $colW;
            // card background
            $pdf->SetFillColor(...self::LIGHT);
            $pdf->Rect($x, $y, $colW - 2, 18, 'F');
            // accent top bar
            $pdf->SetFillColor(...self::BRAND);
            $pdf->Rect($x, $y, $colW - 2, 2, 'F');
            // label
            $pdf->SetXY($x, $y + 3);
            $pdf->SetFont('Arial', '', 7);
            $pdf->SetTextColor(...self::GREY);
            $pdf->Cell($colW - 2, 4, $this->enc(mb_strtoupper($label, 'UTF-8')), 0, 0, 'C');
            // value
            $pdf->SetXY($x, $y + 8);
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->SetTextColor(...self::BRAND);
            $pdf->Cell($colW - 2, 9, $this->enc($value), 0, 0, 'C');
        }

        $pdf->SetXY(self::MARGIN, $y + 22);
        $pdf->SetTextColor(...self::BLACK);
    }

    /**
     * @param string[] $headers
     * @param array<string[]> $rows
     * @param int[] $widths  must sum to PAGE_W
     */
    private function table(object $pdf, array $headers, array $rows, array $widths): void
    {
        $rowH = 7;

        // Header row
        $pdf->SetFillColor(...self::BRAND);
        $pdf->SetTextColor(...self::WHITE);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetX(self::MARGIN);
        foreach ($headers as $ci => $h) {
            $align = $ci === 0 ? 'L' : 'C';
            $pdf->Cell($widths[$ci], $rowH, $this->enc($h), 1, 0, $align, true);
        }
        $pdf->Ln();

        // Data rows
        $pdf->SetTextColor(...self::BLACK);
        $pdf->SetFont('Arial', '', 8);
        $even = false;
        foreach ($rows as $row) {
            if ($pdf->GetY() > 260) {
                $pdf->AddPage();
                // Re-draw header on continuation page
                $pdf->SetFillColor(...self::BRAND);
                $pdf->SetTextColor(...self::WHITE);
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->SetX(self::MARGIN);
                foreach ($headers as $ci => $h) {
                    $pdf->Cell($widths[$ci], $rowH, $this->enc($h), 1, 0, $ci === 0 ? 'L' : 'C', true);
                }
                $pdf->Ln();
                $pdf->SetTextColor(...self::BLACK);
                $pdf->SetFont('Arial', '', 8);
            }

            $even = ! $even;
            $pdf->SetFillColor($even ? 248 : 255, $even ? 248 : 255, $even ? 252 : 255);
            $pdf->SetX(self::MARGIN);
            foreach ($row as $ci => $cell) {
                $align = $ci === 0 ? 'L' : ($ci === 1 && isset($widths[2]) ? 'L' : 'C');
                $pdf->Cell($widths[$ci], $rowH, $this->enc((string) $cell), 1, 0, $align, $even);
            }
            $pdf->Ln();
        }

        $pdf->Ln(3);
    }

    /** Totals footer row after a table */
    private function totalsRow(object $pdf, array $cells, array $widths): void
    {
        $pdf->SetFillColor(220, 222, 235);
        $pdf->SetTextColor(...self::BRAND);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetX(self::MARGIN);
        foreach ($cells as $ci => $cell) {
            $pdf->Cell($widths[$ci], 7, $this->enc($cell), 1, 0, $ci <= 1 ? 'L' : 'C', true);
        }
        $pdf->Ln();
        $pdf->SetTextColor(...self::BLACK);
    }
}
