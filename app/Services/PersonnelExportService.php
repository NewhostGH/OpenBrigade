<?php

namespace App\Services;

use App\Models\Personnel;
use App\Models\Section;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Sabre\VObject\Component\VCard;

class PersonnelExportService
{
    // ── VCard ─────────────────────────────────────────────────────────────────

    public function buildVcard(Personnel $personnel): string
    {
        $vcard = new VCard([
            'FN' => trim(strtoupper($personnel->P_NOM).' '.ucfirst(mb_strtolower($personnel->P_PRENOM))),
            'N' => [strtoupper($personnel->P_NOM), ucfirst(mb_strtolower($personnel->P_PRENOM)), '', '', ''],
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

    // ── Livret data (client-side PDF) ─────────────────────────────────────────

    public function logbookData(Personnel $personnel): array
    {
        $pid = (int) $personnel->P_ID;

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
                'e.EQ_NOM', 'p.TYPE', 'p.DESCRIPTION',
                DB::raw("DATE_FORMAT(q.Q_EXPIRATION, '%d-%m-%Y') as Q_EXPIRATION")
            )
            ->get();

        $oneYearAgo = now()->subYear()->format('Y-m-d');
        $today = now()->format('Y-m-d');

        $formations = $this->getActivities($pid, 'C_FOR', $oneYearAgo, $today);
        $secours = $this->getActivities($pid, 'C_SEC', $oneYearAgo, $today);
        $operations = $this->getActivities($pid, 'C_OPE', $oneYearAgo, $today);

        $categories = DB::table('categorie_evenement')->orderBy('CEV_CODE')->select('CEV_CODE', 'CEV_DESCRIPTION')->get();
        $currentYear = (int) date('Y');

        // 5-year summary: [category_code => [year => hours]]
        $summaryData = [];
        foreach ($categories as $cat) {
            $summaryData[$cat->CEV_CODE] = [];
            for ($i = 4; $i >= 0; $i--) {
                $yr = $currentYear - $i;
                $summaryData[$cat->CEV_CODE][$yr] = $this->getHoursForYear($pid, $cat->CEV_CODE, $yr);
            }
        }

        $civilite = DB::table('type_civilite')
            ->where('TC_ID', $personnel->P_CIVILITE)
            ->value('TC_LIBELLE') ?? '';

        $section = $personnel->section;
        $departement = $section->parent->S_DESCRIPTION ?? $section?->S_DESCRIPTION;

        return [
            'nom' => mb_strtoupper($personnel->P_NOM),
            'prenom' => ucfirst(mb_strtolower($personnel->P_PRENOM)),
            'civilite' => $civilite,
            'code' => $personnel->P_CODE,
            'grade' => $personnel->P_GRADE,
            'birthdate' => $personnel->P_BIRTHDATE?->format('d/m/Y'),
            'birthplace' => trim(($personnel->P_BIRTHPLACE ?? '').($personnel->P_BIRTH_DEP ? ' ('.$personnel->P_BIRTH_DEP.')' : '')),
            'address' => $personnel->P_ADDRESS,
            'zip_city' => trim(($personnel->P_ZIP_CODE ?? '').' '.($personnel->P_CITY ?? '')),
            'phone' => $personnel->P_PHONE,
            'email' => $personnel->P_EMAIL,
            'date_engagement' => $personnel->P_DATE_ENGAGEMENT?->format('d-m-Y'),
            'section' => $section?->S_DESCRIPTION,
            'antenne' => $section?->S_DESCRIPTION,
            'departement' => $departement,
            'letterhead' => $this->letterheadConfig($section),
            'photo_url' => $this->resolvePhotoUrl($personnel),
            'medals' => $medals,
            'indiv_medals' => $indivMedals,
            'diplomes' => $diplomes,
            'qualifications' => $qualifications,
            'formations' => $formations,
            'secours' => $secours,
            'operations' => $operations,
            'summary_5y' => [
                'categories' => $categories->map(fn ($c) => ['code' => $c->CEV_CODE, 'label' => ucfirst(mb_strtolower($c->CEV_DESCRIPTION))])->values(),
                'data' => $summaryData,
            ],
        ];
    }

    // ── Carte data (client-side PDF) ──────────────────────────────────────────

    public function cardData(Personnel $personnel): array
    {
        return [
            'nom' => mb_strtoupper($personnel->P_NOM),
            'prenom' => ucfirst(mb_strtolower($personnel->P_PRENOM)),
            'grade' => $personnel->P_GRADE,
            'code' => $personnel->P_CODE,
            'section' => $personnel->section?->S_DESCRIPTION,
            'photo_url' => $this->resolvePhotoUrl($personnel),
            'badge_url' => $this->badgeUrl($personnel->section),
            'app_name' => config('app.name', 'OpenBrigade'),
        ];
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Letterhead ("Papier à entête") config for client-side pdf-lib rendering.
     * Falls back to the generic public/pdf/pdf_page.pdf when the section has
     * no custom letterhead. Margins follow the legacy eBrigade defaults.
     * Public: also used by the bilan annuel PDFs (StatisticsController).
     */
    public function letterheadConfig(?Section $section): array
    {
        $url = asset('pdf/pdf_page.pdf');
        $file = basename(trim((string) $section?->S_PDF_PAGE));

        if ($section && $file !== ''
            && Storage::disk('local')->exists("sections/{$section->S_ID}/pdf/{$file}")) {
            $url = route('organization.sections.letterhead', $section->S_ID);
        }

        return [
            'pdf_url' => $url,
            'marge_top' => (float) ($section->S_PDF_MARGE_TOP ?? 15) ?: 15,
            'marge_left' => (float) ($section->S_PDF_MARGE_LEFT ?? 15) ?: 15,
            'texte_top' => (float) ($section->S_PDF_TEXTE_TOP ?? 40) ?: 40,
            'texte_bottom' => (float) ($section->S_PDF_TEXTE_BOTTOM ?? 25) ?: 25,
        ];
    }

    /** "Image de fond du badge" URL when the section has one. */
    private function badgeUrl(?Section $section): ?string
    {
        $file = basename(trim((string) $section?->S_PDF_BADGE));

        if ($section && $file !== ''
            && Storage::disk('local')->exists("sections/{$section->S_ID}/images/{$file}")) {
            return route('organization.sections.badge', $section->S_ID);
        }

        return null;
    }

    private function resolvePhotoUrl(Personnel $personnel): ?string
    {
        if (! $personnel->P_PHOTO) {
            return null;
        }

        return route('personnel.photo', $personnel->P_ID);
    }

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
            ->whereRaw('YEAR(eh.EH_DATE_FIN) = ?', [$year])
            ->select('eh.EH_DUREE', 'ep.EP_DUREE')
            ->get();

        $total = 0;
        foreach ($rows as $r) {
            $total += ($r->EP_DUREE > 0) ? $r->EP_DUREE : $r->EH_DUREE;
        }

        return (int) round($total);
    }
}
