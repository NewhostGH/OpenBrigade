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

use App\Models\Cotisation;
use App\Models\ObGroup;
use App\Models\ObUserAssignment;
use App\Models\Personnel;
use App\Models\Poste;
use App\Models\Qualification;
use App\Models\Section;
use App\Models\TypePaiement;
use App\Services\PersonnelExportService;
use App\Services\TableExportService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PersonnelController extends Controller
{
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

        // Load all sections once for hierarchy building + section options
        $allSections = Section::query()
            ->orderBy('S_CODE')
            ->get(['S_ID', 'S_CODE', 'S_DESCRIPTION', 'S_PARENT']);

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

        $sectionOptions = $this->buildSectionTree($allSections);

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
            'sectionOptions' => $sectionOptions,
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
                'value' => fn ($p) => route('personnel.grade_image', ['grade' => $p->P_GRADE ?: 'NR']),
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
            [
                'key' => 'section',
                'label' => 'Section',
                'type' => 'text',
                'value' => fn ($p) => $p->section?->S_CODE ?: '—',
                'mobile' => false,
                'default' => true,
                'exportable' => true,
                'exportValue' => fn ($p) => $p->section?->S_CODE ?? '',
            ],
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
     * Build a depth-annotated flat list of sections in DFS (tree) order.
     * Sections with S_PARENT = 0 are considered top-level (depth 0).
     */
    private function buildSectionTree(Collection $sections, int $parentId = 0, int $depth = 0): array
    {
        $result = [];
        foreach ($sections as $section) {
            if ((int) ($section->S_PARENT ?? 0) === $parentId) {
                $result[] = [
                    'S_ID' => (int) $section->S_ID,
                    'S_CODE' => $section->S_CODE,
                    'S_DESCRIPTION' => $section->S_DESCRIPTION,
                    'depth' => $depth,
                ];
                array_push($result, ...$this->buildSectionTree($sections, (int) $section->S_ID, $depth + 1));
            }
        }

        return $result;
    }

    /**
     * Return all descendant section IDs of a given section via BFS.
     */
    private function getDescendantSectionIds(Collection $allSections, int $parentId): array
    {
        $ids = [];
        $queue = [$parentId];
        while (! empty($queue)) {
            $current = array_shift($queue);
            $children = $allSections
                ->filter(fn ($s) => (int) ($s->S_PARENT ?? 0) === $current)
                ->pluck('S_ID');
            foreach ($children as $childId) {
                $ids[] = (int) $childId;
                $queue[] = (int) $childId;
            }
        }

        return $ids;
    }

    /**
     * Qualifications list for the section (expiry tracking).
     */
    public function qualifications(Request $request): View
    {
        $user = auth()->user();
        $sectionId = (int) $user->P_SECTION;

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

        $items = $query->paginate(50)->withQueryString();

        return view('personnel.qualifications', compact('items', 'filter')
            + ['columns' => $this->qualificationsColumns()]);
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

        $postes = Poste::query()
            ->orderBy('TYPE')
            ->get(['PS_ID', 'TYPE', 'DESCRIPTION', 'PS_EXPIRABLE']);

        $typesPaiement = TypePaiement::query()
            ->orderBy('TP_DESCRIPTION')
            ->get(['TP_ID', 'TP_DESCRIPTION']);

        $periodes = DB::table('periode')->orderBy('P_ORDER')->get(['P_CODE', 'P_DESCRIPTION']);

        $gps = DB::table('gps')->where('P_ID', $personnel->P_ID)->first();

        // Section-scoped role assignments (ob_user_assignment).
        $roleAssignments = DB::table('ob_user_assignment as a')
            ->join('ob_group as g', 'g.id', '=', 'a.group_id')
            ->leftJoin('section as s', 's.S_ID', '=', 'a.section_id')
            ->where('a.person_id', $personnel->P_ID)
            ->orderBy('s.S_DESCRIPTION')
            ->get(['a.id', 'a.section_id', 'g.name as role_name', 's.S_DESCRIPTION as section_name'])
            ->each(fn ($r) => $r->section_name = $r->section_name ?: 'Section '.$r->section_id);

        $allSections = Section::query()->orderBy('S_DESCRIPTION')->get(['S_ID', 'S_DESCRIPTION'])
            ->each(fn ($s) => $s->S_DESCRIPTION = $s->S_DESCRIPTION ?: 'Section '.$s->S_ID);
        $roleGroups = ObGroup::roles()->orderBy('name')->get(['id', 'name']);

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
            ['id' => 'section-disponibilite', 'icon' => 'fas fa-calendar-day',  'label' => 'Disponibilité'],
            ['id' => 'section-calendrier',    'icon' => 'fas fa-calendar',      'label' => 'Calendrier'],
            ['id' => 'section-absences',      'icon' => 'fas fa-user-times',    'label' => 'Absences'],
            ['id' => 'section-historique',    'icon' => 'fas fa-history',       'label' => 'Historique'],
            ['id' => 'section-geo',           'icon' => 'fas fa-map-marker-alt', 'label' => 'Géolocalisation'],
            ['id' => 'section-acces',         'icon' => 'fas fa-shield-alt',    'label' => 'Accès'],
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
            'allSections' => $allSections,
            'roleGroups' => $roleGroups,
        ]);
    }

    // ── Section-scoped role assignments (ob_user_assignment) ─────────────────

    public function roleStore(Request $request, Personnel $personnel): RedirectResponse
    {
        $validated = $request->validate([
            'section_id' => ['required', 'integer', 'exists:section,S_ID'],
            'group_id' => ['required', 'integer', 'exists:ob_group,id'],
        ]);

        abort_unless(
            ObGroup::roles()->whereKey($validated['group_id'])->exists(),
            422,
            'Le groupe choisi n’est pas un rôle organisationnel.'
        );

        ObUserAssignment::firstOrCreate([
            'person_id' => $personnel->P_ID,
            'section_id' => $validated['section_id'],
            'group_id' => $validated['group_id'],
        ]);

        return redirect()->route('personnel.show', $personnel)
            ->with('success', 'Rôle attribué.');
    }

    public function roleDestroy(Personnel $personnel, int $assignment): RedirectResponse
    {
        ObUserAssignment::where('id', $assignment)
            ->where('person_id', $personnel->P_ID)
            ->delete();

        return redirect()->route('personnel.show', $personnel)
            ->with('success', 'Rôle retiré.');
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

    public function storeCotisation(Request $request, Personnel $personnel)
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

        Cotisation::create($validated);

        return redirect()->route('personnel.show', $personnel)
            ->with('success', 'Cotisation enregistrée.');
    }

    public function updateCotisation(Request $request, Personnel $personnel, int $pcId)
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

        Cotisation::where('PC_ID', $pcId)
            ->where('P_ID', $personnel->P_ID)
            ->update($validated);

        return redirect()->route('personnel.show', $personnel)
            ->with('success', 'Cotisation mise à jour.');
    }

    public function destroyCotisation(Personnel $personnel, int $pcId)
    {
        Cotisation::where('PC_ID', $pcId)
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
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportLivret(Personnel $personnel)
    {
        $personnel->load('section');
        $service = new PersonnelExportService;
        $pdf = $service->buildLivret($personnel);

        $filename = Str::ascii('Livret_'.strtoupper($personnel->P_NOM).'_'.$personnel->P_PRENOM).'.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }

    public function exportCarte(Personnel $personnel)
    {
        $personnel->load('section');
        $service = new PersonnelExportService;
        $pdf = $service->buildCarte($personnel);

        $filename = Str::ascii('Carte_'.strtoupper($personnel->P_NOM).'_'.$personnel->P_PRENOM).'.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
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

        if ($sectionId > 0) {
            if ($subsections) {
                $allSections = Section::query()->get(['S_ID', 'S_CODE', 'S_DESCRIPTION', 'S_PARENT']);
                $descendants = $this->getDescendantSectionIds($allSections, $sectionId);
                $query->whereIn('P_SECTION', array_merge([$sectionId], $descendants));
            } else {
                $query->where('P_SECTION', $sectionId);
            }
        }

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
        $sections = Section::query()->orderBy('S_CODE')->get(['S_ID', 'S_CODE', 'S_DESCRIPTION']);
        $groupes = ObGroup::groups()->orderBy('name')->get(['id', 'name']);
        $companies = DB::table('company')->orderBy('C_NAME')->get(['C_ID', 'C_NAME']);

        return view('personnel.form', [
            'personnel' => null,
            'sections' => $sections,
            'groupes' => $groupes,
            'companies' => $companies,
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
            'P_SECTION' => 'nullable|integer|exists:section,S_ID',
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
            'P_BIRTHDATE' => 'nullable|date',
            'P_BIRTHPLACE' => 'nullable|string|max:50',
            'P_BIRTH_DEP' => 'nullable|string|max:3',
            'P_LICENCE' => 'nullable|string|max:30',
            'P_LICENCE_DATE' => 'nullable|date',
            'P_LICENCE_EXPIRY' => 'nullable|date',
            'GP_ID' => 'nullable|integer',
            'GP_ID2' => 'nullable|integer',
            'C_ID' => 'nullable|integer|exists:company,C_ID',
            'DATE_NPAI' => 'nullable|date',
            'OBSERVATION' => 'nullable|string',
            'photo_upload' => 'nullable|image|max:4096',
        ]);

        $validated['P_HIDE'] = $request->boolean('P_HIDE');
        $validated['P_NOSPAM'] = $request->boolean('P_NOSPAM');
        $validated['NPAI'] = $request->boolean('NPAI');
        $validated['SUSPENDU'] = $request->boolean('SUSPENDU');
        $validated['P_OLD_MEMBER'] = 0;

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

        return redirect()
            ->route('personnel.show', $personnel)
            ->with('success', 'Personnel créé avec succès.');
    }

    public function edit(Personnel $personnel): View
    {
        $sections = Section::query()
            ->orderBy('S_CODE')
            ->get(['S_ID', 'S_CODE', 'S_DESCRIPTION']);

        $groupes = ObGroup::groups()
            ->orderBy('name')
            ->get(['id', 'name']);

        $companies = DB::table('company')
            ->orderBy('C_NAME')
            ->get(['C_ID', 'C_NAME']);

        return view('personnel.form', [
            'personnel' => $personnel,
            'sections' => $sections,
            'groupes' => $groupes,
            'companies' => $companies,
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
            'P_SECTION' => 'nullable|integer|exists:section,S_ID',
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
            // Personal info
            'P_BIRTHDATE' => 'nullable|date',
            'P_BIRTHPLACE' => 'nullable|string|max:50',
            'P_BIRTH_DEP' => 'nullable|string|max:3',
            // Licence
            'P_LICENCE' => 'nullable|string|max:30',
            'P_LICENCE_DATE' => 'nullable|date',
            'P_LICENCE_EXPIRY' => 'nullable|date',
            // Access / organisation
            'GP_ID' => 'nullable|integer',
            'GP_ID2' => 'nullable|integer',
            'C_ID' => 'nullable|integer|exists:company,C_ID',
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

        return redirect()
            ->route('personnel.show', $personnel)
            ->with('success', 'Fiche personnel mise à jour.');
    }
}
