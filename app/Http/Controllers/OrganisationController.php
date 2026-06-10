<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OrganisationController extends Controller
{
    /**
     * Fixed reference data for the Agréments & Médailles tab.
     * 'type' => 'period' uses Début/Fin columns.
     * 'type' => 'medal'  uses Délivrée le / Agrafe (A_DEBUT / A_COMMENT).
     */
    private const AGREMENT_CATEGORIES = [
        [
            'key' => 'securite_civile', 'label' => 'Agréments de sécurité civile', 'type' => 'period',
            'items' => [
                ['code' => 'A-Aqua', 'label' => 'Sauvetage aquatique'],
                ['code' => 'A1',     'label' => 'Opérations de secours à personnes et sauvetage'],
                ['code' => 'A2',     'label' => 'Recherche cynophile'],
                ['code' => 'B',      'label' => 'Actions de soutien aux populations sinistrées'],
                ['code' => 'C',      'label' => 'Encadrement des bénévoles lors des actions de soutien'],
                ['code' => 'D',      'label' => 'Dispositif prévisionnel de secours — agrément'],
                ['code' => 'D-Aqua', 'label' => 'Sécurité de la pratique des activités aquatiques'],
            ],
        ],
        [
            'key' => 'conv_missions', 'label' => 'Conventions de missions', 'type' => 'period',
            'items' => [
                ['code' => '37', 'label' => 'Missions de secours d\'urgence aux personnes'],
                ['code' => '38', 'label' => 'Actions de soutien aux populations et de formation'],
            ],
        ],
        [
            'key' => 'conv_specifiques', 'label' => 'Conventions spécifiques', 'type' => 'period',
            'items' => [
                ['code' => 'AUTRE', 'label' => 'Convention Spécifique autre'],
                ['code' => 'CUMP',  'label' => 'Convention CUMP'],
                ['code' => 'ERDF',  'label' => 'Convention avec ERDF'],
                ['code' => 'PCS',   'label' => 'Convention Plans Communaux de Sauvegarde'],
                ['code' => 'PREF',  'label' => 'Convention avec la Préfecture'],
                ['code' => 'SNCF',  'label' => 'Convention avec la SNCF'],
                ['code' => 'TRIP',  'label' => 'Convention tripartite'],
            ],
        ],
        [
            'key' => 'formation_entreprise', 'label' => 'Formation Entreprise', 'type' => 'period',
            'items' => [
                ['code' => 'APS-ASD', 'label' => 'Acteur Prévention Secours / Aide et soins à domicile'],
                ['code' => 'PRAP',    'label' => 'Formation Prévention des Risques liés à l\'Activité Physique'],
                ['code' => 'SST',     'label' => 'Formation Sauveteur Secouriste du Travail'],
            ],
        ],
        [
            'key' => 'formations_secourisme', 'label' => 'Formations au secourisme', 'type' => 'period',
            'items' => [
                ['code' => 'BNSSA',   'label' => 'Formations au B.N.S.S.A'],
                ['code' => 'GQS',     'label' => 'Sensibilisation aux Gestes Qui Sauvent'],
                ['code' => 'PAE-PS',  'label' => 'Formation de formateur aux Premiers Secours'],
                ['code' => 'PAE-PSC', 'label' => 'Formation de formateur en Prévention et Secours Civiques de niveau 1'],
                ['code' => 'PS',      'label' => 'Formation de formateur aux Premiers Secours'],
                ['code' => 'PSC1',    'label' => 'Formation Prévention et Secours Civiques de niveau 1'],
            ],
        ],
        [
            'key' => 'formations_specifiques', 'label' => 'Formations spécifiques', 'type' => 'period',
            'items' => [
                ['code' => 'CE',   'label' => 'Chef d\'équipe'],
                ['code' => 'CP',   'label' => 'Chef de poste'],
                ['code' => 'PSSP', 'label' => 'Premiers Secours Socio-psychologiques'],
                ['code' => 'SC',   'label' => 'Secourisme canin'],
            ],
        ],
        [
            'key' => 'infos_association', 'label' => 'Informations liées à l\'association', 'type' => 'period',
            'items' => [
                ['code' => 'AUT',   'label' => 'Autorisation d\'exercice'],
                ['code' => 'CONTR', 'label' => 'Contribution fédérale'],
                ['code' => 'COTIS', 'label' => 'Cotisation fédérale'],
            ],
        ],
        [
            'key' => 'medailles', 'label' => 'Médailles collectives', 'type' => 'medal',
            'items' => [
                ['code' => 'CD', 'label' => 'Acte de Courage et de Dévouement'],
                ['code' => 'GO', 'label' => 'Médaille Grand Or de la Sécurité Civile'],
            ],
        ],
    ];

    // ── Organisation overview ─────────────────────────────────────────────────

    public function index(): View
    {
        $user = auth()->user();
        $sectionId = (int) $user->P_SECTION;

        $sections = DB::table('section')
            ->where('S_INACTIVE', 0)
            ->orderBy('S_ORDER')
            ->orderBy('S_CODE')
            ->get(['S_ID', 'S_PARENT', 'S_CODE', 'S_DESCRIPTION', 'S_PHONE', 'S_EMAIL']);

        $memberCounts = $this->memberCounts();

        $tree = $this->buildTree($sections, 0, $memberCounts);

        return view('organisation.index', compact('tree', 'sectionId'));
    }

    // ── Sections list + CRUD ──────────────────────────────────────────────────

    public function sections(): View
    {
        $sections = DB::table('section as s')
            ->leftJoin('section as p', 'p.S_ID', '=', 's.S_PARENT')
            ->orderBy('s.S_ORDER')
            ->orderBy('s.S_CODE')
            ->get([
                's.S_ID', 's.S_PARENT', 's.S_CODE', 's.S_DESCRIPTION', 's.S_CITY',
                's.S_PHONE', 's.S_EMAIL', 's.S_ORDER', 's.S_INACTIVE',
                'p.S_DESCRIPTION as parent_name', 'p.S_CODE as parent_code',
            ])
            ->each(fn ($s) => $s->S_DESCRIPTION = $s->S_DESCRIPTION ?: 'Section '.$s->S_ID);

        $counts = $this->memberCounts();

        return view('organisation.sections', compact('sections', 'counts'));
    }

    public function showSection(Section $section): View
    {
        $section->load('parent');

        // Organigramme: roles held in this section, grouped by ob_group
        $orgRows = DB::table('ob_user_assignment as ua')
            ->join('pompier as p', 'ua.person_id', '=', 'p.P_ID')
            ->join('ob_group as g', 'ua.group_id', '=', 'g.id')
            ->where('ua.section_id', $section->S_ID)
            ->where('g.kind', 'role')
            ->orderBy('g.ordering')
            ->orderBy('p.P_NOM')
            ->get(['p.P_ID', 'p.P_NOM', 'p.P_PRENOM', 'p.P_CODE', 'g.name as role_name', 'g.ordering']);

        $orgByRole = $orgRows->groupBy('role_name');

        // Agréments from the legacy `agrement` table, keyed by TA_CODE
        $agrementsMap = DB::table('agrement')
            ->where('S_ID', $section->S_ID)
            ->get()
            ->keyBy('TA_CODE');

        $agrementCategories = self::AGREMENT_CATEGORIES;

        // RIB from compte_bancaire
        $rib = DB::table('compte_bancaire')
            ->where('CB_TYPE', 'S')
            ->where('CB_ID', $section->S_ID)
            ->first();

        $memberCount = (int) ($this->memberCounts()[$section->S_ID] ?? 0);

        return view('organisation.section-show', compact(
            'section', 'orgByRole', 'agrementsMap', 'agrementCategories', 'rib', 'memberCount'
        ));
    }

    public function createSection(): View
    {
        return view('organisation.section-form', [
            'section' => null,
            'parents' => $this->parentOptions(null),
        ]);
    }

    public function storeSection(Request $request): RedirectResponse
    {
        $data = $this->validateSection($request);

        $data['S_ID'] = (int) (DB::table('section')->max('S_ID') ?? 0) + 1;
        $data['S_INACTIVE'] = $request->boolean('S_INACTIVE');

        $section = Section::create($data);

        return redirect()->route('organisation.sections.show', $section->S_ID)
            ->with('success', 'Section créée.');
    }

    public function editSection(Section $section): View
    {
        return view('organisation.section-form', [
            'section' => $section,
            'parents' => $this->parentOptions((int) $section->S_ID),
        ]);
    }

    public function updateSection(Request $request, Section $section): RedirectResponse
    {
        $data = $this->validateSection($request);
        $data['S_INACTIVE'] = $request->boolean('S_INACTIVE');

        $section->update($data);

        return redirect()->route('organisation.sections.show', $section->S_ID)
            ->with('success', 'Section mise à jour.');
    }

    public function destroySection(Section $section): RedirectResponse
    {
        $hasChildren = DB::table('section')->where('S_PARENT', $section->S_ID)->exists();
        if ($hasChildren) {
            return redirect()->route('organisation.sections')
                ->with('error', 'Cette section a des sous-sections — déplacez-les d\'abord.');
        }

        $hasMembers = DB::table('pompier')->where('P_SECTION', $section->S_ID)->exists();
        if ($hasMembers) {
            return redirect()->route('organisation.sections')
                ->with('error', 'Cette section contient des membres — réaffectez-les d\'abord.');
        }

        $section->delete();

        return redirect()->route('organisation.sections')
            ->with('success', 'Section supprimée.');
    }

    // ── Personnalisation tab ──────────────────────────────────────────────────

    public function updatePersonalisation(Request $request, Section $section): RedirectResponse
    {
        $data = $request->validate([
            'S_PDF_MARGE_TOP' => ['nullable', 'numeric', 'min:0', 'max:999'],
            'S_PDF_MARGE_LEFT' => ['nullable', 'numeric', 'min:0', 'max:999'],
            'S_PDF_TEXTE_TOP' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'S_PDF_TEXTE_BOTTOM' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'NB_DAYS_BEFORE_BLOCK' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'S_PDF_SIGNATURE' => ['nullable', 'string'],
            'S_DEVIS_DEBUT' => ['nullable', 'string'],
            'S_DEVIS_FIN' => ['nullable', 'string'],
            'S_FACTURE_DEBUT' => ['nullable', 'string'],
            'S_FACTURE_FIN' => ['nullable', 'string'],
            'S_PDF_PAGE' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'S_PDF_BADGE' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif', 'max:2048'],
            'S_IMAGE_SIGNATURE' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif', 'max:2048'],
        ]);

        foreach (['S_PDF_PAGE' => 'pdf', 'S_PDF_BADGE' => 'images', 'S_IMAGE_SIGNATURE' => 'images'] as $field => $subDir) {
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store("sections/{$section->S_ID}/{$subDir}", 'public');
                $data[$field] = basename($path);
            } else {
                unset($data[$field]);
            }
        }

        // 0 = jamais in the legacy schema
        if (! array_key_exists('NB_DAYS_BEFORE_BLOCK', $data) || $data['NB_DAYS_BEFORE_BLOCK'] === null) {
            $data['NB_DAYS_BEFORE_BLOCK'] = 0;
        }

        $section->update($data);

        return redirect()->route('organisation.sections.show', [$section->S_ID, 'tab' => 'personalisation'])
            ->with('success', 'Personnalisation enregistrée.');
    }

    // ── Section PDF assets (letterhead / badge, used by client-side pdf-lib) ──

    public function sectionLetterhead(Section $section)
    {
        $file = basename(trim((string) $section->S_PDF_PAGE));
        abort_if($file === '', 404);

        $path = Storage::disk('public')->path("sections/{$section->S_ID}/pdf/{$file}");
        abort_unless(file_exists($path), 404);

        return response()->file($path, ['Content-Type' => 'application/pdf']);
    }

    public function sectionBadge(Section $section)
    {
        $file = basename(trim((string) $section->S_PDF_BADGE));
        abort_if($file === '', 404);

        $path = Storage::disk('public')->path("sections/{$section->S_ID}/images/{$file}");
        abort_unless(file_exists($path), 404);

        return response()->file($path);
    }

    /**
     * Remove the section's custom letterhead so PDFs fall back to the
     * generic public/pdf/pdf_page.pdf template.
     */
    public function resetLetterhead(Section $section): RedirectResponse
    {
        $file = basename(trim((string) $section->S_PDF_PAGE));
        if ($file !== '') {
            Storage::disk('public')->delete("sections/{$section->S_ID}/pdf/{$file}");
        }

        $section->update(['S_PDF_PAGE' => '']);

        return redirect()->route('organisation.sections.show', [$section->S_ID, 'tab' => 'personalisation'])
            ->with('success', 'Papier à entête réinitialisé — le modèle par défaut sera utilisé.');
    }

    public function resetBadge(Section $section): RedirectResponse
    {
        $file = basename(trim((string) $section->S_PDF_BADGE));
        if ($file !== '') {
            Storage::disk('public')->delete("sections/{$section->S_ID}/images/{$file}");
        }

        $section->update(['S_PDF_BADGE' => '']);

        return redirect()->route('organisation.sections.show', [$section->S_ID, 'tab' => 'personalisation'])
            ->with('success', 'Image de fond du badge réinitialisée.');
    }

    // ── Cotisation / RIB tab ──────────────────────────────────────────────────

    public function updateRib(Request $request, Section $section): RedirectResponse
    {
        $validated = $request->validate([
            'IBAN' => ['nullable', 'string', 'max:34'],
            'BIC' => ['nullable', 'string', 'max:11'],
        ]);

        $iban = preg_replace('/\s+/', '', strtoupper($validated['IBAN'] ?? ''));
        $bic = trim(strtoupper($validated['BIC'] ?? ''));

        DB::table('compte_bancaire')
            ->where('CB_TYPE', 'S')
            ->where('CB_ID', $section->S_ID)
            ->delete();

        if ($iban || $bic) {
            DB::table('compte_bancaire')->insert([
                'CB_TYPE' => 'S',
                'CB_ID' => $section->S_ID,
                'IBAN' => $iban,
                'BIC' => $bic,
                'UPDATE_DATE' => now(),
            ]);
        }

        return redirect()->route('organisation.sections.show', [$section->S_ID, 'tab' => 'cotisation'])
            ->with('success', 'RIB enregistré.');
    }

    // ── Agréments AJAX ────────────────────────────────────────────────────────

    public function upsertAgrement(Request $request, Section $section, string $code): JsonResponse
    {
        $validated = $request->validate([
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date'],
            'agrafe' => ['nullable', 'string', 'max:100'],
        ]);

        // Always delete first (legacy pattern: delete then re-insert)
        DB::table('agrement')
            ->where('TA_CODE', $code)
            ->where('S_ID', $section->S_ID)
            ->delete();

        if ($validated['date_debut'] || $validated['date_fin'] || $validated['agrafe']) {
            DB::table('agrement')->insert([
                'TA_CODE' => $code,
                'S_ID' => $section->S_ID,
                'A_DEBUT' => $validated['date_debut'] ?: null,
                'A_FIN' => $validated['date_fin'] ?: null,
                'A_COMMENT' => $validated['agrafe'] ?: null,
                'TAV_ID' => null,
            ]);
        }

        return response()->json(['ok' => true]);
    }

    public function destroyAgrement(Section $section, string $code): JsonResponse
    {
        DB::table('agrement')
            ->where('TA_CODE', $code)
            ->where('S_ID', $section->S_ID)
            ->delete();

        return response()->json(['ok' => true]);
    }

    // ── Cartographie ──────────────────────────────────────────────────────────

    public function cartographie(): View
    {
        $rows = DB::table('gps as g')
            ->join('pompier as p', 'g.P_ID', '=', 'p.P_ID')
            ->leftJoin('section as s', 'p.P_SECTION', '=', 's.S_ID')
            ->where('p.P_OLD_MEMBER', 0)
            ->where('p.GP_ID', '<>', -1)
            ->whereNotNull('g.LAT')->whereNotNull('g.LNG')
            ->where('g.LAT', '<>', 0)->where('g.LNG', '<>', 0)
            ->groupBy('p.P_SECTION', 's.S_CODE', 's.S_DESCRIPTION')
            ->select(
                'p.P_SECTION',
                's.S_CODE',
                's.S_DESCRIPTION',
                DB::raw('AVG(g.LAT) as lat'),
                DB::raw('AVG(g.LNG) as lng'),
                DB::raw('COUNT(*) as cnt')
            )
            ->get();

        $markers = $rows->map(function ($r) {
            $label = ($r->S_CODE ? $r->S_CODE.' — ' : '').($r->S_DESCRIPTION ?: 'Section '.$r->P_SECTION);

            return [
                'name' => $label,
                'grade' => $r->cnt.' membre'.($r->cnt > 1 ? 's' : ''),
                'section' => '',
                'phone' => '',
                'address' => $r->cnt.' membre'.($r->cnt > 1 ? 's' : '').' géolocalisé'.($r->cnt > 1 ? 's' : ''),
                'lat' => (float) $r->lat,
                'lng' => (float) $r->lng,
                'photo_url' => '',
                'profile_url' => route('geolocalisation.index', ['section' => $r->P_SECTION]),
            ];
        })->values()->toArray();

        return view('organisation.cartographie', [
            'markers' => $markers,
            'count' => count($markers),
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function memberCounts()
    {
        return DB::table('pompier')
            ->where('P_OLD_MEMBER', 0)
            ->where('GP_ID', '<>', -1)
            ->whereNull('P_FIN')
            ->groupBy('P_SECTION')
            ->select('P_SECTION', DB::raw('COUNT(*) as cnt'))
            ->pluck('cnt', 'P_SECTION');
    }

    private function parentOptions(?int $excludeId)
    {
        return DB::table('section')
            ->when($excludeId, fn ($q) => $q->where('S_ID', '<>', $excludeId))
            ->orderBy('S_CODE')
            ->get(['S_ID', 'S_CODE', 'S_DESCRIPTION'])
            ->each(fn ($s) => $s->S_DESCRIPTION = $s->S_DESCRIPTION ?: 'Section '.$s->S_ID);
    }

    /** @return array<string,mixed> */
    private function validateSection(Request $request): array
    {
        return $request->validate([
            // Informations obligatoires
            'S_CODE' => ['required', 'string', 'max:25'],
            'S_DESCRIPTION' => ['nullable', 'string', 'max:80'],
            'S_ORDER' => ['nullable', 'integer', 'min:0', 'max:255'],
            'S_PARENT' => ['nullable', 'integer'],
            // Contact
            'S_PHONE' => ['nullable', 'string', 'max:20'],
            'S_PHONE2' => ['nullable', 'string', 'max:20'],
            'S_PHONE3' => ['nullable', 'string', 'max:20'],
            'S_FAX' => ['nullable', 'string', 'max:20'],
            'S_EMAIL' => ['nullable', 'email', 'max:60'],
            'S_EMAIL2' => ['nullable', 'email', 'max:60'],
            'S_EMAIL3' => ['nullable', 'email', 'max:60'],
            'S_WHATSAPP' => ['nullable', 'string', 'max:30'],
            'S_ID_RADIO' => ['nullable', 'string', 'max:5'],
            // Informations facultatives
            'S_ADDRESS' => ['nullable', 'string', 'max:150'],
            'S_ADDRESS_COMPLEMENT' => ['nullable', 'string', 'max:150'],
            'S_ZIP_CODE' => ['nullable', 'string', 'max:6'],
            'S_CITY' => ['nullable', 'string', 'max:30'],
            'S_SIRET' => ['nullable', 'string', 'max:20'],
            'S_AFFILIATION' => ['nullable', 'string', 'max:20'],
            'S_URL' => ['nullable', 'string', 'max:60'],
        ]);
    }

    private function buildTree($sections, int $parentId, $memberCounts): array
    {
        return $sections
            ->filter(function ($s) use ($parentId) {
                $p = (int) ($s->S_PARENT ?? 0);

                return $parentId === 0 ? ($p === 0) : $p === $parentId;
            })
            ->map(fn ($s) => [
                'section' => $s,
                'count' => (int) ($memberCounts[$s->S_ID] ?? 0),
                'children' => $this->buildTree($sections, (int) $s->S_ID, $memberCounts),
            ])
            ->values()
            ->all();
    }
}
