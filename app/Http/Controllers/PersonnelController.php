<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

namespace App\Http\Controllers;

use App\Models\Dues;
use App\Models\ObGroup;
use App\Models\ObPersonnelGroup;
use App\Models\ObPersonnelSection;
use App\Models\ObUserAssignment;
use App\Models\PaymentType;
use App\Models\Personnel;
use App\Models\Position;
use App\Models\Qualification;
use App\Models\Section;
use App\Services\FeatureService;
use App\Services\PersonnelExportService;
use App\Services\SectionScopeService;
use App\Services\TableExportService;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PersonnelController extends Controller
{
    public function __construct(
        private readonly FeatureService $features,
        private readonly SectionScopeService $sectionScope,
    ) {}

    public function index(Request $request): View
    {
        $position = (string) $request->string('position', 'actif');
        $search = trim((string) $request->string('q'));
        $category = (string) $request->string('category', 'INT');
        $sectionId = (int) $request->integer('section', 0);
        $order = (string) $request->string('order', 'P_NOM');
        $subsections = (bool) $request->integer('subsections', 1);
        $perPage = (int) $request->integer('perPage', 100);
        if (! in_array($perPage, [12, 24, 48, 100, 500], true)) {
            $perPage = 100;
        }

        $items = $this->buildFilteredQuery($request)
            ->with(['section'])
            ->select([
                'P_ID', 'P_PHOTO', 'P_CODE', 'P_PRENOM', 'P_NOM', 'P_STATUT', 'P_GRADE',
                'P_SECTION', 'P_EMAIL', 'P_PHONE', 'P_PHONE2', 'P_CIVILITE',
                'P_BIRTHDATE', 'P_DATE_ENGAGEMENT', 'P_OLD_MEMBER', 'GP_ID', 'P_FIN',
                'P_CITY', 'P_ADDRESS', 'P_ZIP_CODE', 'P_PROFESSION', 'P_SEXE',
                'P_LICENCE', 'P_LICENCE_EXPIRY',
            ])
            ->paginate($perPage)
            ->withQueryString();

        return view('personnel.index', [
            'items' => $items,
            'columns' => $this->personnelColumns(),
            'position' => $position,
            'search' => $search,
            'category' => $category,
            'sectionId' => $sectionId,
            'order' => $order,
            'subsections' => $subsections,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Single source of truth for personnel list columns — used by both
     * the index view (via <x-ob-table>) and the XLS/CSV export.
     */
    private function personnelColumns(): array
    {
        $statutMap = Personnel::statutBadgeMap();

        return [
            // ── Always-on columns ──────────────────────────────────────────
            [
                'key' => 'photo',
                'label' => 'Photo',
                'type' => 'avatar',
                'value' => fn ($p) => $p->getAvatarUrl(),
                'imageClass' => 'ob-avatar-sm',
                'cardShow' => true,
                'mobile' => true,
                'default' => true,
                'exportable' => false,
            ],
            [
                'key' => 'grade',
                'label' => 'Grade',
                'type' => 'image',
                'value' => fn ($p) => route('personnel.grade-image', ['grade' => $p->P_GRADE ?: 'NR']),
                'imageAlt' => fn ($p) => $p->P_GRADE ?: '',
                'imageClass' => 'ob-grade-img',
                'imageError' => "this.outerHTML='<small class=\"text-muted\">' + this.alt + '</small>'",
                'imageLazy' => false,
                'mobile' => true,
                'default' => true,
                'exportable' => true,
                'exportValue' => fn ($p) => $p->P_GRADE ?? '',
            ],
            [
                'key' => 'name',
                'label' => 'Nom Prénom',
                'type' => 'html',
                'value' => fn ($p) => '<strong style="font-size:var(--font-size-sm);">'
                                          .e(strtoupper($p->P_NOM)).' '
                                          .e(ucfirst(mb_strtolower($p->P_PRENOM)))
                                          .'</strong>',
                'cardShow' => true,
                'mobile' => true,
                'alwaysVisible' => true,
                'exportable' => false, // nom + prénom are always exported separately as two columns
                'sortField' => 'P_NOM',
            ],
            // ── Default-visible optional columns ──────────────────────────
            [
                'key' => 'birthdate',
                'label' => 'Date naissance',
                'type' => 'date',
                'value' => fn ($p) => $p->P_BIRTHDATE,
                'mobile' => false,
                'default' => true,
                'exportable' => true,
                'sortField' => 'P_BIRTHDATE',
            ],
            [
                'key' => 'phone',
                'label' => 'Téléphone',
                'type' => 'html',
                'value' => fn ($p) => implode('<br>', array_filter([$p->P_PHONE, $p->P_PHONE2])) ?: '—',
                'mobile' => false,
                'default' => true,
                'exportable' => true,
                'exportValue' => fn ($p) => $p->P_PHONE ?? '',
            ],
            [
                'key' => 'code',
                'label' => 'Matricule',
                'type' => 'text',
                'value' => fn ($p) => $p->P_CODE,
                'mobile' => false,
                'default' => true,
                'exportable' => true,
                'sortField' => 'P_CODE',
            ],
            // The home/principal section only means something with several sites.
            ...($this->features->isEnabled('multi_site') ? [[
                'key' => 'section',
                'label' => 'Section principale',
                'type' => 'text',
                'value' => fn ($p) => $p->section?->S_CODE ?: '—',
                'mobile' => false,
                'default' => true,
                'exportable' => true,
                'exportValue' => fn ($p) => $p->section->S_CODE ?? '',
            ]] : []),
            [
                'key' => 'entree',
                'label' => "Date d'entrée",
                'type' => 'date',
                'value' => fn ($p) => $p->P_DATE_ENGAGEMENT,
                'mobile' => false,
                'default' => true,
                'exportable' => true,
                'sortField' => 'P_DATE_ENGAGEMENT',
            ],
            [
                'key' => 'statut',
                'label' => 'Statut',
                'type' => 'badge',
                'value' => fn ($p) => $p->P_STATUT,
                'badgeMap' => $statutMap,
                'cardShow' => true,
                'mobile' => false,
                'default' => true,
                'exportable' => true,
                'exportValue' => fn ($p) => $statutMap[$p->P_STATUT][0] ?? $p->P_STATUT,
            ],
            [
                'key' => 'etat',
                'label' => 'Position',
                'type' => 'badge',
                'value' => fn ($p) => $p->etat,
                'badgeMap' => config('personnel.etat_badges'),
                'mobile' => false,
                'default' => true,
                'exportable' => true,
            ],
            // ── Hidden-by-default optional columns ────────────────────────
            [
                'key' => 'email',
                'label' => 'Email',
                'type' => 'text',
                'value' => fn ($p) => $p->P_EMAIL ?? '',
                'mobile' => false,
                'default' => false,
                'exportable' => true,
            ],
            [
                'key' => 'phone2',
                'label' => 'Tél. 2',
                'type' => 'text',
                'value' => fn ($p) => $p->P_PHONE2 ?? '',
                'mobile' => false,
                'default' => false,
                'exportable' => true,
            ],
            [
                'key' => 'city',
                'label' => 'Ville',
                'type' => 'text',
                'value' => fn ($p) => $p->P_CITY ?? '',
                'mobile' => false,
                'default' => false,
                'exportable' => true,
                'exportValue' => fn ($p) => trim(implode(' ', array_filter([
                    $p->P_ADDRESS ?? '',
                    ($p->P_ZIP_CODE ?? '').' '.($p->P_CITY ?? ''),
                ]))),
            ],
            [
                'key' => 'profession',
                'label' => 'Profession',
                'type' => 'text',
                'value' => fn ($p) => $p->P_PROFESSION ?? '',
                'mobile' => false,
                'default' => false,
                'exportable' => true,
            ],
            [
                'key' => 'sexe',
                'label' => 'Sexe',
                'type' => 'text',
                'value' => fn ($p) => $p->P_SEXE ?? '',
                'mobile' => false,
                'default' => false,
                'exportable' => true,
                'thWidth' => '50px',
            ],
            [
                'key' => 'fin',
                'label' => 'Date de fin',
                'type' => 'date',
                'value' => fn ($p) => $p->P_FIN,
                'mobile' => false,
                'default' => false,
                'exportable' => true,
                'sortField' => 'P_FIN',
            ],
            [
                'key' => 'licence',
                'label' => 'Permis',
                'type' => 'text',
                'value' => fn ($p) => $p->P_LICENCE ?? '',
                'mobile' => false,
                'default' => false,
                'exportable' => true,
            ],
            [
                'key' => 'licenceExpiry',
                'label' => 'Exp. permis',
                'type' => 'date',
                'value' => fn ($p) => $p->P_LICENCE_EXPIRY,
                'mobile' => false,
                'default' => false,
                'exportable' => true,
            ],
        ];
    }

    /**
     * Serve grade badge images from the legacy assets directory.
     */
    public function gradeImage(string $grade)
    {
        $grade = preg_replace('/[^A-Z0-9]/', '', strtoupper($grade));

        // TODO: Migrate code — grade images live in archive/legacy_app; move to storage/ after decommission
        $path = base_path("archive/legacy_app/images/grades_sp/{$grade}.png");
        if (! File::exists($path)) {
            $path = base_path('archive/legacy_app/images/grades_sp/NR.png');
        }
        if (File::exists($path)) {
            return response()->file($path, ['Cache-Control' => 'public, max-age=86400']);
        }

        // 1×1 transparent PNG fallback
        return response(
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='),
            200,
            ['Content-Type' => 'image/png']
        );
    }

    /**
     * Section rows visible to the current user, for the form's membership
     * multiselect and role-assignment selects (SectionScopeService is the
     * single isolation authority).
     */
    private function visibleSections(): Collection
    {
        $query = Section::query()
            ->orderByRaw('(S_CODE IS NULL), S_CODE, S_DESCRIPTION');

        $visible = $this->sectionScope->visibleSectionIds();
        if ($visible !== null) {
            $query->whereIn('S_ID', $visible);
        }

        return $query->get(['S_ID', 'S_CODE', 'S_DESCRIPTION']);
    }

    /**
     * Qualifications list for the section (expiry tracking).
     */
    public function qualifications(Request $request): View
    {
        $filter = (string) $request->string('filter', 'all'); // all|expiring|expired

        $items = $this->buildQualificationsQuery($request)->paginate(50)->withQueryString();

        return view('personnel.qualifications', compact('items', 'filter')
            + ['columns' => $this->qualificationsColumns()]);
    }

    /**
     * Section-scoped qualifications query (with status + filter) shared by the
     * list and the exports.
     */
    private function buildQualificationsQuery(Request $request): Builder
    {
        $sectionId = (int) auth()->user()->P_SECTION;
        $filter = (string) $request->string('filter', 'all'); // all|expiring|expired

        $today = now()->toDateString();
        $warn30 = now()->addDays(30)->toDateString();

        $query = DB::table('qualification as q')
            ->join('pompier as p', 'q.P_ID', '=', 'p.P_ID')
            ->join('poste as ps', 'q.PS_ID', '=', 'ps.PS_ID')
            ->where('p.P_SECTION', $sectionId)
            ->where('p.P_OLD_MEMBER', 0)
            ->select(
                'p.P_ID', 'p.P_NOM', 'p.P_PRENOM',
                'ps.PS_ID', 'ps.TYPE as PS_TYPE',
                'q.Q_VAL', 'q.Q_EXPIRATION',
                DB::raw("CASE
                    WHEN q.Q_EXPIRATION IS NOT NULL AND q.Q_EXPIRATION < '{$today}' THEN 'expired'
                    WHEN q.Q_EXPIRATION IS NOT NULL AND q.Q_EXPIRATION <= '{$warn30}' THEN 'expiring'
                    ELSE 'ok'
                END as status")
            )
            ->orderBy('p.P_NOM')
            ->orderBy('p.P_PRENOM')
            ->orderBy('ps.TYPE');

        if ($filter === 'expiring') {
            $query->whereRaw("q.Q_EXPIRATION IS NOT NULL AND q.Q_EXPIRATION > '{$today}' AND q.Q_EXPIRATION <= '{$warn30}'");
        } elseif ($filter === 'expired') {
            $query->whereRaw("q.Q_EXPIRATION IS NOT NULL AND q.Q_EXPIRATION < '{$today}'");
        }

        return $query;
    }

    /**
     * Stream the qualifications list as an XLSX download (qualifications_xls.php
     * parity). Honours the active filter and the ?cols= selection.
     */
    public function exportQualificationsXls(Request $request)
    {
        return $this->exportQualifications($request, 'xlsx');
    }

    /**
     * Stream the qualifications list as a CSV download.
     */
    public function exportQualificationsCsv(Request $request)
    {
        return $this->exportQualifications($request, 'csv');
    }

    private function exportQualifications(Request $request, string $format)
    {
        $service = new TableExportService;
        // 'personnel' / 'type' are alwaysVisible, so resolveColumns skips them.
        $columns = $service->resolveColumns($this->qualificationsColumns(), $request, [
            ['Personnel', fn ($q) => $q->P_PRENOM.' '.strtoupper((string) $q->P_NOM)],
            ['Type',      fn ($q) => $q->PS_TYPE ?? ''],
        ]);

        $items = $this->buildQualificationsQuery($request)->get();
        $filename = 'Qualifications_'.date('Ymd');

        return $format === 'csv'
            ? $service->toCsv($columns, $items, $filename)
            : $service->toXlsx($columns, $items, $filename, ['sheetTitle' => 'Qualifications', 'freezeHeader' => true]);
    }

    private function qualificationsColumns(): array
    {
        return [
            ['key' => 'personnel', 'label' => 'Personnel', 'type' => 'html', 'value' => fn ($q) => '<a href="'.route('personnel.show', $q->P_ID).'" class="text-decoration-none">'.e($q->P_PRENOM.' '.strtoupper($q->P_NOM)).'</a>', 'alwaysVisible' => true, 'mobile' => true, 'exportable' => true, 'exportValue' => fn ($q) => $q->P_PRENOM.' '.$q->P_NOM],
            ['key' => 'type', 'label' => 'Type', 'type' => 'text', 'value' => fn ($q) => $q->PS_TYPE ?? '—', 'alwaysVisible' => true, 'mobile' => true, 'exportable' => true, 'exportValue' => fn ($q) => $q->PS_TYPE ?? ''],
            ['key' => 'valeur', 'label' => 'Valeur', 'type' => 'text', 'value' => fn ($q) => $q->Q_VAL ?? '—', 'mobile' => false, 'exportable' => true, 'exportValue' => fn ($q) => $q->Q_VAL ?? ''],
            ['key' => 'expiration', 'label' => 'Expiration', 'type' => 'date', 'value' => fn ($q) => $q->Q_EXPIRATION, 'mobile' => false, 'exportable' => true, 'exportValue' => fn ($q) => $q->Q_EXPIRATION ? Carbon::parse($q->Q_EXPIRATION)->format('d/m/Y') : ''],
            ['key' => 'statut', 'label' => 'Statut', 'type' => 'badge', 'value' => fn ($q) => $q->status ?? 'ok', 'badgeMap' => ['expired' => ['Expirée', 'ob-badge-bloqued'], 'expiring' => ['Expire bientôt', 'ob-badge-ben'], 'ok' => ['Valide', 'ob-badge-actif']], 'exportable' => true, 'exportValue' => fn ($q) => $q->status === 'expired' ? 'Expirée' : ($q->status === 'expiring' ? 'Expire bientôt' : 'Valide'), 'mobile' => true],
        ];
    }

    public function photo(Personnel $personnel)
    {
        $filename = trim((string) $personnel->P_PHOTO);

        $noCache = [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        if ($filename !== '') {
            $filename = basename($filename);
            // TODO: Migrate code — trombi photos live in archive/legacy_app; move to storage/ after decommission
            $paths = [
                base_path('archive/legacy_app/images/user-specific/trombi/'.$filename),
                public_path('images/user-specific/trombi/'.$filename),
            ];

            foreach ($paths as $path) {
                if (File::exists($path)) {
                    return response()->file($path, $noCache);
                }
            }
        }

        // TODO: Migrate code — DEFAULT.png lives in archive/legacy_app; move to storage/ after decommission
        $defaultPath = base_path('archive/legacy_app/images/user-specific/DEFAULT.png');
        if (File::exists($defaultPath)) {
            return response()->file($defaultPath, $noCache);
        }

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"><rect width="64" height="64" fill="#e2e8f0"/><circle cx="32" cy="24" r="12" fill="#94a3b8"/><rect x="14" y="40" width="36" height="18" rx="9" fill="#94a3b8"/></svg>';

        return response($svg, 200)->withHeaders($noCache)->header('Content-Type', 'image/svg+xml');
    }

    public function show(Personnel $personnel): View
    {
        $personnel->load(['section', 'groupe', 'groupe2', 'qualifications.poste', 'cotisations.typePaiement']);

        // Linked company
        $company = $personnel->C_ID
            ? DB::table('company')->where('C_ID', $personnel->C_ID)->first(['C_ID', 'C_NAME'])
            : null;

        // Participation — last 50 events (dates come from evenement_horaire)
        $participation = DB::table('evenement_participation as ep')
            ->join('evenement as e', 'ep.E_CODE', '=', 'e.E_CODE')
            ->leftJoin(
                DB::raw('(SELECT E_CODE, MIN(EH_DATE_DEBUT) as first_date FROM evenement_horaire GROUP BY E_CODE) as eh'),
                'e.E_CODE', '=', 'eh.E_CODE'
            )
            ->where('ep.P_ID', $personnel->P_ID)
            ->select(
                'e.E_CODE', 'e.E_LIBELLE',
                'eh.first_date as E_DATE_DEBUT',
                'ep.EP_DUREE', 'ep.EP_ABSENT', 'ep.EP_EXCUSE', 'ep.EP_KM'
            )
            ->orderByDesc('eh.first_date')
            ->limit(50)
            ->get();

        $postes = Position::query()
            ->orderBy('TYPE')
            ->get(['PS_ID', 'TYPE', 'DESCRIPTION', 'PS_EXPIRABLE']);

        $typesPaiement = PaymentType::query()
            ->orderBy('TP_DESCRIPTION')
            ->get(['TP_ID', 'TP_DESCRIPTION']);

        $periodes = DB::table('periode')->orderBy('P_ORDER')->get(['P_CODE', 'P_DESCRIPTION']);

        $gps = DB::table('gps')->where('P_ID', $personnel->P_ID)->first();

        // All group memberships (ob_personnel_group).
        $personnelGroups = DB::table('ob_personnel_group as pg')
            ->join('ob_group as g', 'g.id', '=', 'pg.group_id')
            ->where('pg.person_id', $personnel->P_ID)
            ->orderBy('g.name')
            ->pluck('g.name');

        // All section memberships (ob_personnel_section).
        $personnelSections = DB::table('ob_personnel_section as ps')
            ->join('section as s', 's.S_ID', '=', 'ps.section_id')
            ->where('ps.person_id', $personnel->P_ID)
            ->orderBy('s.S_CODE')
            ->get(['s.S_CODE', 's.S_DESCRIPTION']);

        // Section-scoped role assignments (ob_user_assignment).
        $roleAssignments = DB::table('ob_user_assignment as a')
            ->join('ob_group as g', 'g.id', '=', 'a.group_id')
            ->leftJoin('section as s', 's.S_ID', '=', 'a.section_id')
            ->where('a.person_id', $personnel->P_ID)
            ->orderBy('s.S_DESCRIPTION')
            ->get(['a.id', 'a.section_id', 'g.name as role_name', 's.S_CODE as section_code', 's.S_DESCRIPTION as section_name'])
            ->each(fn ($r) => $r->section_name = $r->section_id == 0
                ? '— global —'
                : ($r->section_code ? $r->section_code.($r->section_name ? ' — '.$r->section_name : '') : 'Section '.$r->section_id));

        $cotisations = $personnel->cotisations->sortByDesc('ANNEE');
        $today = now()->toDateString();
        $warn30 = now()->addDays(30)->toDateString();

        $sideNav = [
            ['id' => 'section-info',          'icon' => 'fas fa-user',          'label' => 'Information'],
            ['id' => 'section-competences',   'icon' => 'fas fa-certificate',   'label' => 'Compétences',
                'badge' => $personnel->qualifications->count() ?: null],
            ['id' => 'section-cotisations',   'icon' => 'fas fa-euro-sign',     'label' => 'Cotisations',
                'badge' => $cotisations->count() ?: null],
            ['id' => 'section-participation', 'icon' => 'fas fa-calendar-check', 'label' => 'Participation',
                'badge' => $participation->count() ?: null],
            ['id' => 'section-dotation',      'icon' => 'fas fa-box',           'label' => 'Dotation'],
            ['id' => 'section-documents',     'icon' => 'fas fa-file-alt',      'label' => 'Documents'],
            ['id' => 'section-notedfrais',    'icon' => 'fas fa-receipt',       'label' => 'Notes de frais'],
            ['id' => 'section-disponibilite', 'icon' => 'fas fa-calendar-day',  'label' => 'Availabilitynibilité'],
            ['id' => 'section-calendrier',    'icon' => 'fas fa-calendar',      'label' => 'Calendrier'],
            ['id' => 'section-absences',      'icon' => 'fas fa-user-times',    'label' => 'Absences'],
            ['id' => 'section-geo',           'icon' => 'fas fa-map-marker-alt', 'label' => 'Géolocalisation'],
            ['id' => 'section-acces',         'icon' => 'fas fa-shield-alt',    'label' => 'Accès'],
            ['id' => 'section-historique',    'icon' => 'fas fa-history',       'label' => 'Historique'],
        ];

        return view('personnel.show', [
            'personnel' => $personnel,
            'groupe2' => $personnel->groupe2,
            'company' => $company,
            'participation' => $participation,
            'postes' => $postes,
            'typesPaiement' => $typesPaiement,
            'periodes' => $periodes,
            'gps' => $gps,
            'cotisations' => $cotisations,
            'today' => $today,
            'warn30' => $warn30,
            'sideNav' => $sideNav,
            'roleAssignments' => $roleAssignments,
            'personnelGroups' => $personnelGroups,
            'personnelSections' => $personnelSections,
        ]);
    }

    /**
     * Sync section memberships (ob_personnel_section) from the `sections[]`
     * form input. Guarded by permission 9.
     */
    private function syncSections(Request $request, Personnel $personnel): void
    {
        if (! $request->user()->hasPermission(9)) {
            return;
        }

        // The editor can only add/remove memberships inside their own visible
        // scope; memberships outside it are preserved untouched (the form
        // never displayed them, so their absence from sections[] means
        // nothing).
        $editable = $this->sectionScope->visibleSectionIds()
            ?? Section::pluck('S_ID')->map(fn ($v) => (int) $v)->all();

        $desired = collect($request->input('sections', []))
            ->map(fn ($v) => (int) $v)
            ->filter(fn ($v) => in_array($v, $editable, true))
            ->unique();

        // The principal section (P_SECTION) must always be present.
        if ($personnel->P_SECTION !== null) {
            $desired->push((int) $personnel->P_SECTION);
        }
        $desired = $desired->unique()->values();

        ObPersonnelSection::where('person_id', $personnel->P_ID)
            ->whereIn('section_id', $editable)
            ->where('section_id', '!=', (int) $personnel->P_SECTION)
            ->whereNotIn('section_id', $desired)
            ->delete();

        $existing = ObPersonnelSection::where('person_id', $personnel->P_ID)
            ->pluck('section_id')
            ->map(fn ($v) => (int) $v)
            ->all();

        foreach ($desired as $sectionId) {
            if (! in_array($sectionId, $existing, true)) {
                ObPersonnelSection::create([
                    'person_id' => $personnel->P_ID,
                    'section_id' => $sectionId,
                ]);
            }
        }
    }

    /**
     * Sync role assignments (ob_user_assignment) from the `role_assignments[]`
     * form input. Each entry carries a group_id and an optional section_id.
     * Guarded by permission 9.
     */
    private function syncRoles(Request $request, Personnel $personnel): void
    {
        if (! $request->user()->hasPermission(9)) {
            return;
        }

        $validRoleIds = ObGroup::roles()->pluck('id')->map(fn ($v) => (int) $v)->all();

        // Section-scoped roles can only be granted/revoked inside the
        // editor's visible scope; assignments tied to a section outside it
        // are preserved untouched (the form never displayed them).
        $editable = $this->sectionScope->visibleSectionIds()
            ?? Section::pluck('S_ID')->map(fn ($v) => (int) $v)->all();

        $desired = collect($request->input('role_assignments', []))
            ->filter(fn ($a) => isset($a['group_id']) && in_array((int) $a['group_id'], $validRoleIds, true))
            ->map(fn ($a) => [
                'person_id' => $personnel->P_ID,
                'group_id' => (int) $a['group_id'],
                // 0 = global sentinel; non-zero only if it's a visible section.
                'section_id' => ! empty($a['section_id']) && in_array((int) $a['section_id'], $editable, true)
                    ? (int) $a['section_id']
                    : 0,
            ])
            ->unique(fn ($a) => $a['group_id'].'-'.$a['section_id'])
            ->values();

        ObUserAssignment::where('person_id', $personnel->P_ID)
            ->where(fn ($q) => $q->whereIn('section_id', $editable)->orWhere('section_id', 0)->orWhereNull('section_id'))
            ->delete();

        foreach ($desired as $row) {
            ObUserAssignment::create($row);
        }
    }

    /**
     * Sync group memberships (ob_personnel_group) from the `groups[]`
     * form input. Guarded by permission 9.
     */
    private function syncGroups(Request $request, Personnel $personnel): void
    {
        if (! $request->user()->hasPermission(9)) {
            return;
        }

        $validGroupIds = ObGroup::groups()->where('id', '!=', -1)->pluck('id')->map(fn ($v) => (int) $v)->all();

        $desired = collect($request->input('groups', []))
            ->map(fn ($v) => (int) $v)
            ->filter(fn ($v) => $v > 0 && in_array($v, $validGroupIds, true))
            ->unique()
            ->values();

        ObPersonnelGroup::where('person_id', $personnel->P_ID)
            ->where('group_id', '!=', -1)
            ->delete();

        foreach ($desired as $groupId) {
            ObPersonnelGroup::create([
                'person_id' => $personnel->P_ID,
                'group_id' => $groupId,
            ]);
        }
    }

    // ── Qualifications (competences) CRUD ────────────────────────────────────

    public function storeQualification(Request $request, Personnel $personnel)
    {
        $validated = $request->validate([
            'PS_ID' => ['required', 'integer', 'exists:poste,PS_ID'],
            'Q_VAL' => ['nullable', 'string', 'max:100'],
            'Q_EXPIRATION' => ['nullable', 'date'],
        ]);

        $existing = Qualification::where('P_ID', $personnel->P_ID)
            ->where('PS_ID', $validated['PS_ID'])
            ->exists();

        if ($existing) {
            return redirect()->route('personnel.show', $personnel)
                ->with('error', 'Cette compétence est déjà enregistrée pour ce membre.');
        }

        Qualification::create([
            'P_ID' => $personnel->P_ID,
            'PS_ID' => $validated['PS_ID'],
            'Q_VAL' => $validated['Q_VAL'] ?: null,
            'Q_EXPIRATION' => $validated['Q_EXPIRATION'] ?: null,
            'Q_UPDATED_BY' => auth()->id(),
            'Q_UPDATE_DATE' => now(),
        ]);

        return redirect()->route('personnel.show', $personnel)
            ->with('success', 'Compétence ajoutée.');
    }

    public function updateQualification(Request $request, Personnel $personnel, int $psId)
    {
        $validated = $request->validate([
            'Q_VAL' => ['nullable', 'string', 'max:100'],
            'Q_EXPIRATION' => ['nullable', 'date'],
        ]);

        Qualification::where('P_ID', $personnel->P_ID)
            ->where('PS_ID', $psId)
            ->update([
                'Q_VAL' => $validated['Q_VAL'] ?: null,
                'Q_EXPIRATION' => $validated['Q_EXPIRATION'] ?: null,
                'Q_UPDATED_BY' => auth()->id(),
                'Q_UPDATE_DATE' => now(),
            ]);

        return redirect()->route('personnel.show', $personnel)
            ->with('success', 'Compétence mise à jour.');
    }

    public function destroyQualification(Personnel $personnel, int $psId)
    {
        Qualification::where('P_ID', $personnel->P_ID)
            ->where('PS_ID', $psId)
            ->delete();

        return redirect()->route('personnel.show', $personnel)
            ->with('success', 'Compétence supprimée.');
    }

    // ── Cotisations CRUD ─────────────────────────────────────────────────────

    public function storeDues(Request $request, Personnel $personnel)
    {
        $validated = $request->validate([
            'ANNEE' => ['required', 'integer', 'min:1990', 'max:2100'],
            'PERIODE_CODE' => ['nullable', 'string', 'max:20'],
            'PC_DATE' => ['required', 'date'],
            'MONTANT' => ['required', 'numeric', 'min:0'],
            'TP_ID' => ['nullable', 'integer', 'exists:type_paiement,TP_ID'],
            'REMBOURSEMENT' => ['boolean'],
            'COMMENTAIRE' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['P_ID'] = $personnel->P_ID;
        $validated['REMBOURSEMENT'] = $request->boolean('REMBOURSEMENT') ? 1 : 0;
        $validated['TP_ID'] = $validated['TP_ID'] ?? 0;

        Dues::create($validated);

        return redirect()->route('personnel.show', $personnel)
            ->with('success', 'Cotisation enregistrée.');
    }

    public function updateDues(Request $request, Personnel $personnel, int $pcId)
    {
        $validated = $request->validate([
            'ANNEE' => ['required', 'integer', 'min:1990', 'max:2100'],
            'PERIODE_CODE' => ['nullable', 'string', 'max:20'],
            'PC_DATE' => ['required', 'date'],
            'MONTANT' => ['required', 'numeric', 'min:0'],
            'TP_ID' => ['nullable', 'integer', 'exists:type_paiement,TP_ID'],
            'REMBOURSEMENT' => ['boolean'],
            'COMMENTAIRE' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['REMBOURSEMENT'] = $request->boolean('REMBOURSEMENT') ? 1 : 0;
        $validated['TP_ID'] = $validated['TP_ID'] ?? 0;

        Dues::where('PC_ID', $pcId)
            ->where('P_ID', $personnel->P_ID)
            ->update($validated);

        return redirect()->route('personnel.show', $personnel)
            ->with('success', 'Dues mise à jour.');
    }

    public function destroyDues(Personnel $personnel, int $pcId)
    {
        Dues::where('PC_ID', $pcId)
            ->where('P_ID', $personnel->P_ID)
            ->delete();

        return redirect()->route('personnel.show', $personnel)
            ->with('success', 'Cotisation supprimée.');
    }

    // ── Exports ─────────────────────────────────────────────────────────────────

    public function exportXls(Request $request)
    {
        $service = new TableExportService;
        $columns = $service->resolveColumns($this->personnelColumns(), $request, [
            ['Nom',    fn ($p) => strtoupper($p->P_NOM)],
            ['Prénom', fn ($p) => ucfirst(mb_strtolower($p->P_PRENOM))],
        ]);
        $items = $this->buildFilteredQuery($request)->with(['section'])->get($this->exportSelectFields());

        return $service->toXlsx($columns, $items, 'Personnel_'.date('Ymd'), ['sheetTitle' => 'Personnel']);
    }

    public function exportCsv(Request $request)
    {
        $service = new TableExportService;
        $columns = $service->resolveColumns($this->personnelColumns(), $request, [
            ['Nom',    fn ($p) => strtoupper($p->P_NOM)],
            ['Prénom', fn ($p) => ucfirst(mb_strtolower($p->P_PRENOM))],
        ]);
        $items = $this->buildFilteredQuery($request)->with(['section'])->get($this->exportSelectFields());

        return $service->toCsv($columns, $items, 'Personnel_'.date('Ymd'));
    }

    private function exportSelectFields(): array
    {
        return [
            'P_CODE', 'P_NOM', 'P_PRENOM', 'P_STATUT', 'P_GRADE',
            'P_SECTION', 'P_EMAIL', 'P_PHONE', 'P_PHONE2',
            'P_BIRTHDATE', 'P_DATE_ENGAGEMENT', 'P_OLD_MEMBER', 'GP_ID', 'P_FIN',
            'P_CITY', 'P_ADDRESS', 'P_ZIP_CODE', 'P_PROFESSION', 'P_SEXE',
            'P_LICENCE', 'P_LICENCE_EXPIRY',
        ];
    }

    public function exportVcard(Personnel $personnel)
    {
        $personnel->load('section');
        $service = new PersonnelExportService;
        $vcf = $service->buildVcard($personnel);

        $filename = Str::ascii(strtoupper($personnel->P_NOM).'_'.$personnel->P_PRENOM).'.vcf';

        return response($vcf, 200, [
            'Content-Type' => 'text/vcard; charset=UTF-8',
            'Content-Availabilitysition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function logbookData(Personnel $personnel)
    {
        $personnel->load('section');

        return response()->json((new PersonnelExportService)->logbookData($personnel));
    }

    public function cardData(Personnel $personnel)
    {
        $personnel->load('section');

        return response()->json((new PersonnelExportService)->cardData($personnel));
    }

    // ── Shared query builder (used by index() and the XLS/CSV exports) ─────────

    private function buildFilteredQuery(Request $request)
    {
        $position = (string) $request->string('position', 'actif');
        $search = trim((string) $request->string('q'));
        $category = (string) $request->string('category', 'INT');
        $sectionId = (int) $request->integer('section', 0);
        $order = (string) $request->string('order', 'P_NOM');
        $subsections = (bool) $request->integer('subsections', 1);

        $allowedOrder = [
            'P_NOM', 'P_PRENOM', 'P_CODE', 'P_STATUT', 'P_GRADE',
            'P_DATE_ENGAGEMENT', 'P_FIN', 'P_BIRTHDATE',
        ];
        if (! in_array($order, $allowedOrder, true)) {
            $order = 'P_NOM';
        }

        $query = Personnel::query();

        if ($position === 'actif') {
            $query->where('P_OLD_MEMBER', 0)->where('GP_ID', '<>', -1);
        } elseif ($position === 'archive') {
            $query->where('P_OLD_MEMBER', '>', 0);
        } elseif ($position === 'bloqued') {
            $query->where('GP_ID', -1)->where('P_OLD_MEMBER', 0);
        }

        if ($category !== '' && $category !== 'ALL') {
            if ($category === 'INT') {
                $query->where('P_STATUT', '<>', 'EXT');
            } else {
                $query->where('P_STATUT', $category);
            }
        }

        $this->sectionScope->apply($query, 'P_SECTION', $sectionId, $subsections);

        if ($search !== '') {
            $query->where(function ($inner) use ($search): void {
                $inner->where('P_NOM', 'like', "%{$search}%")
                    ->orWhere('P_PRENOM', 'like', "%{$search}%")
                    ->orWhere('P_CODE', 'like', "%{$search}%")
                    ->orWhere('P_EMAIL', 'like', "%{$search}%")
                    ->orWhere('P_PHONE', 'like', "%{$search}%")
                    ->orWhere('P_PHONE2', 'like', "%{$search}%")
                    ->orWhere('P_GRADE', 'like', "%{$search}%")
                    ->orWhere('P_ADDRESS', 'like', "%{$search}%")
                    ->orWhere('P_ZIP_CODE', 'like', "%{$search}%")
                    ->orWhere('P_CITY', 'like', "%{$search}%");
            });
        }

        if (in_array($order, ['P_FIN', 'P_DATE_ENGAGEMENT'], true)) {
            $query->orderByDesc($order);
        } else {
            $query->orderBy($order);
        }

        return $query;
    }

    public function create(): View
    {
        $companies = DB::table('company')->orderBy('C_NAME')->get(['C_ID', 'C_NAME']);
        $allPersonnel = Personnel::query()
            ->where('P_OLD_MEMBER', 0)
            ->orderBy('P_NOM')->orderBy('P_PRENOM')
            ->get(['P_ID', 'P_NOM', 'P_PRENOM', 'P_PHONE', 'P_EMAIL']);

        return view('personnel.form', [
            'personnel' => null,
            'sections' => $this->visibleSections(),
            'companies' => $companies,
            'allPersonnel' => $allPersonnel,
            'allRoles' => ObGroup::roles()->orderBy('name')->get(['id', 'name']),
            'allGroups' => ObGroup::groups()->where('id', '!=', -1)->orderBy('name')->get(['id', 'name']),
            'currentSectionIds' => [],
            'currentRoleAssignments' => [],
            'currentGroupIds' => [],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'P_CIVILITE' => 'nullable|integer',
            'P_CODE' => 'required|string|max:20|unique:pompier,P_CODE',
            'P_PRENOM' => 'required|string|max:25',
            'P_PRENOM2' => 'nullable|string|max:25',
            'P_NOM' => 'required|string|max:30',
            'P_NOM_NAISSANCE' => 'nullable|string|max:30',
            'P_SEXE' => 'nullable|in:M,F',
            'P_GRADE' => 'nullable|string|max:6',
            'P_PROFESSION' => 'nullable|string|max:6',
            'P_STATUT' => 'required|string|max:5',
            'P_SECTION' => 'required|integer|exists:section,S_ID',
            'P_DATE_ENGAGEMENT' => 'nullable|date',
            'P_FIN' => 'nullable|date',
            'P_ABBREGE' => 'nullable|string|max:20',
            'P_EMAIL' => 'nullable|email|max:60',
            'P_PHONE' => 'nullable|string|max:20',
            'P_PHONE2' => 'nullable|string|max:20',
            'P_ADDRESS' => 'nullable|string|max:150',
            'P_ZIP_CODE' => 'nullable|string|max:6',
            'P_CITY' => 'nullable|string|max:30',
            'P_PAYS' => 'nullable|string|max:50',
            'P_RELATION_PRENOM' => 'nullable|string|max:50',
            'P_RELATION_NOM' => 'nullable|string|max:50',
            'P_RELATION_PHONE' => 'nullable|string|max:20',
            'P_RELATION_MAIL' => 'nullable|email|max:100',
            'P_URGENCE_PERSON_ID' => 'nullable|integer|exists:pompier,P_ID',
            'P_BIRTHDATE' => 'nullable|date',
            'P_BIRTHPLACE' => 'nullable|string|max:50',
            'P_BIRTH_DEP' => 'nullable|string|max:3',
            'P_LICENCE' => 'nullable|string|max:30',
            'P_LICENCE_DATE' => 'nullable|date',
            'P_LICENCE_EXPIRY' => 'nullable|date',
            'DATE_NPAI' => 'nullable|date',
            'OBSERVATION' => 'nullable|string',
            'photo_upload' => 'nullable|image|max:4096',
        ]);

        $validated['P_HIDE'] = $request->boolean('P_HIDE');
        $validated['P_NOSPAM'] = $request->boolean('P_NOSPAM');
        $validated['NPAI'] = $request->boolean('NPAI');
        $validated['SUSPENDU'] = $request->boolean('SUSPENDU');
        $validated['P_OLD_MEMBER'] = 0;

        $validated = $this->coerceLegacyNotNullColumns($validated);

        // Enforce section isolation: a member can only be assigned to a
        // section inside the editor's visible scope.
        $validated['P_SECTION'] = $this->sectionScope->coerce((int) $validated['P_SECTION']);

        // pompier.P_MDP is NOT NULL with no default. An empty hash can never
        // match a login attempt, so the member has no account until a
        // password is set (password-reset flow).
        $validated['P_MDP'] = '';

        if (! empty($validated['P_URGENCE_PERSON_ID'])) {
            $linked = Personnel::find((int) $validated['P_URGENCE_PERSON_ID']);
            if ($linked) {
                $validated['P_RELATION_PRENOM'] = $linked->P_PRENOM;
                $validated['P_RELATION_NOM'] = $linked->P_NOM;
                $validated['P_RELATION_PHONE'] = $linked->P_PHONE;
                $validated['P_RELATION_MAIL'] = $linked->P_EMAIL;
            }
        }

        unset($validated['photo_upload']);

        $personnel = Personnel::create($validated);

        if ($request->hasFile('photo_upload') && $request->file('photo_upload')->isValid()) {
            $file = $request->file('photo_upload');
            $extension = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = $personnel->P_ID.'_'.time().'.'.$extension;
            $destDir = public_path('images/user-specific/trombi');

            if (! is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }

            $file->move($destDir, $filename);
            $personnel->update(['P_PHOTO' => $filename]);
        }

        $this->syncSections($request, $personnel);
        $this->syncRoles($request, $personnel);
        $this->syncGroups($request, $personnel);

        return redirect()
            ->route('personnel.show', $personnel)
            ->with('success', 'Personnel créé avec succès.');
    }

    /**
     * Empty optional inputs arrive as NULL (ConvertEmptyStringsToNull), which
     * strict-mode MariaDB rejects on these legacy NOT NULL pompier columns.
     * Coerce them back to their legacy defaults.
     */
    private function coerceLegacyNotNullColumns(array $validated): array
    {
        return array_merge($validated, [
            'P_CIVILITE' => $validated['P_CIVILITE'] ?? 1,
            'P_SEXE' => $validated['P_SEXE'] ?? 'M',
            'P_GRADE' => $validated['P_GRADE'] ?? '',
            'P_PROFESSION' => $validated['P_PROFESSION'] ?? '',
        ]);
    }

    public function edit(Personnel $personnel): View
    {
        $companies = DB::table('company')
            ->orderBy('C_NAME')
            ->get(['C_ID', 'C_NAME']);

        $currentSectionIds = DB::table('ob_personnel_section')
            ->where('person_id', $personnel->P_ID)
            ->pluck('section_id')
            ->map(fn ($v) => (int) $v)
            ->all();

        // Only assignments inside the editor's visible scope (or global ones)
        // are shown and editable; the rest is preserved by syncRoles().
        $visible = $this->sectionScope->visibleSectionIds();
        $currentRoleAssignments = DB::table('ob_user_assignment')
            ->where('person_id', $personnel->P_ID)
            ->get(['group_id', 'section_id'])
            ->map(fn ($r) => [
                'group_id' => (int) $r->group_id,
                'section_id' => (int) $r->section_id, // 0 = global
            ])
            ->filter(fn ($a) => $visible === null
                || $a['section_id'] === 0
                || in_array($a['section_id'], $visible, true))
            ->values()
            ->all();

        $currentGroupIds = DB::table('ob_personnel_group')
            ->where('person_id', $personnel->P_ID)
            ->pluck('group_id')
            ->map(fn ($v) => (int) $v)
            ->all();

        $allPersonnel = Personnel::query()
            ->where('P_OLD_MEMBER', 0)
            ->where('P_ID', '!=', $personnel->P_ID)
            ->orderBy('P_NOM')->orderBy('P_PRENOM')
            ->get(['P_ID', 'P_NOM', 'P_PRENOM', 'P_PHONE', 'P_EMAIL']);

        return view('personnel.form', [
            'personnel' => $personnel,
            'sections' => $this->visibleSections(),
            'companies' => $companies,
            'allPersonnel' => $allPersonnel,
            'allRoles' => ObGroup::roles()->orderBy('name')->get(['id', 'name']),
            'allGroups' => ObGroup::groups()->where('id', '!=', -1)->orderBy('name')->get(['id', 'name']),
            'currentSectionIds' => $currentSectionIds,
            'currentRoleAssignments' => $currentRoleAssignments,
            'currentGroupIds' => $currentGroupIds,
        ]);
    }

    public function update(Request $request, Personnel $personnel)
    {
        $validated = $request->validate([
            // Identity
            'P_CIVILITE' => 'nullable|integer',
            'P_CODE' => [
                'required', 'string', 'max:20',
                Rule::unique('pompier', 'P_CODE')->ignore($personnel->P_ID, 'P_ID'),
            ],
            'P_PRENOM' => 'required|string|max:25',
            'P_PRENOM2' => 'nullable|string|max:25',
            'P_NOM' => 'required|string|max:30',
            'P_NOM_NAISSANCE' => 'nullable|string|max:30',
            'P_SEXE' => 'nullable|in:M,F',
            'P_GRADE' => 'nullable|string|max:6',
            'P_PROFESSION' => 'nullable|string|max:6',
            'P_STATUT' => 'required|string|max:5',
            'P_SECTION' => 'required|integer|exists:section,S_ID',
            'P_DATE_ENGAGEMENT' => 'nullable|date',
            'P_FIN' => 'nullable|date',
            'P_ABBREGE' => 'nullable|string|max:20',
            // Contact
            'P_EMAIL' => 'nullable|email|max:60',
            'P_PHONE' => 'nullable|string|max:20',
            'P_PHONE2' => 'nullable|string|max:20',
            'P_ADDRESS' => 'nullable|string|max:150',
            'P_ZIP_CODE' => 'nullable|string|max:6',
            'P_CITY' => 'nullable|string|max:30',
            'P_PAYS' => 'nullable|string|max:50',
            // Emergency contact
            'P_RELATION_PRENOM' => 'nullable|string|max:50',
            'P_RELATION_NOM' => 'nullable|string|max:50',
            'P_RELATION_PHONE' => 'nullable|string|max:20',
            'P_RELATION_MAIL' => 'nullable|email|max:100',
            'P_URGENCE_PERSON_ID' => 'nullable|integer|exists:pompier,P_ID',
            // Personal info
            'P_BIRTHDATE' => 'nullable|date',
            'P_BIRTHPLACE' => 'nullable|string|max:50',
            'P_BIRTH_DEP' => 'nullable|string|max:3',
            // Licence
            'P_LICENCE' => 'nullable|string|max:30',
            'P_LICENCE_DATE' => 'nullable|date',
            'P_LICENCE_EXPIRY' => 'nullable|date',
            // NPAI
            'DATE_NPAI' => 'nullable|date',
            // Notes
            'OBSERVATION' => 'nullable|string',
            // Photo
            'photo_upload' => 'nullable|image|max:4096',
        ]);

        // Boolean flags (unchecked checkboxes are not submitted)
        $validated['P_HIDE'] = $request->boolean('P_HIDE');
        $validated['P_NOSPAM'] = $request->boolean('P_NOSPAM');
        $validated['NPAI'] = $request->boolean('NPAI');
        $validated['SUSPENDU'] = $request->boolean('SUSPENDU');

        $validated = $this->coerceLegacyNotNullColumns($validated);

        // Enforce section isolation on edit: an out-of-scope target section
        // is rejected and the member keeps their current one.
        if (! $this->sectionScope->allows((int) $validated['P_SECTION'])) {
            $validated['P_SECTION'] = (int) $personnel->P_SECTION;
        }

        if (! empty($validated['P_URGENCE_PERSON_ID'])) {
            $linked = Personnel::find((int) $validated['P_URGENCE_PERSON_ID']);
            if ($linked) {
                $validated['P_RELATION_PRENOM'] = $linked->P_PRENOM;
                $validated['P_RELATION_NOM'] = $linked->P_NOM;
                $validated['P_RELATION_PHONE'] = $linked->P_PHONE;
                $validated['P_RELATION_MAIL'] = $linked->P_EMAIL;
            }
        }

        // Handle photo upload
        if ($request->hasFile('photo_upload') && $request->file('photo_upload')->isValid()) {
            $file = $request->file('photo_upload');
            $extension = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = $personnel->P_ID.'_'.time().'.'.$extension;
            $destDir = public_path('images/user-specific/trombi');

            if (! is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }

            $file->move($destDir, $filename);
            $validated['P_PHOTO'] = $filename;
        }

        // Remove the virtual field before saving
        unset($validated['photo_upload']);

        $personnel->update($validated);

        $this->syncSections($request, $personnel);
        $this->syncRoles($request, $personnel);
        $this->syncGroups($request, $personnel);

        return redirect()
            ->route('personnel.show', $personnel)
            ->with('success', 'Fiche personnel mise à jour.');
    }
}
