<?php

namespace App\Http\Controllers;

use App\Models\Vehicule;
use App\Services\FeatureService;
use App\Services\SectionScopeService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VehiculeController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('q'));
        $filtSect = (int) $request->integer('section', 0);
        $status = (string) $request->string('status', 'all');

        $query = Vehicule::query()
            ->with(['section'])
            ->leftJoin('vehicule_position as vp', 'vp.VP_ID', '=', 'vehicule.VP_ID')
            ->leftJoin('type_vehicule as tv', 'tv.TV_CODE', '=', 'vehicule.TV_CODE')
            ->select(
                'vehicule.V_ID', 'vehicule.V_IMMATRICULATION', 'vehicule.V_INDICATIF',
                'vehicule.TV_CODE', 'vehicule.V_MODELE', 'vehicule.V_ANNEE',
                'vehicule.S_ID', 'vehicule.V_EXTERNE',
                'vehicule.V_FLAG1', 'vehicule.V_FLAG2', 'vehicule.V_FLAG3', 'vehicule.V_FLAG4',
                'vehicule.V_ASS_DATE', 'vehicule.V_CT_DATE',
                'vehicule.V_REV_DATE', 'vehicule.V_TITRE_DATE',
                'vp.VP_LIBELLE', 'vp.VP_OPERATIONNEL',
                'tv.TV_LIBELLE'
            );

        // Section isolation + optional explicit filter (single subtree).
        app(SectionScopeService::class)->apply($query, 'vehicule.S_ID', $filtSect, subsections: false);

        // Operational status filter
        if ($status === 'op') {
            $query->where('vp.VP_OPERATIONNEL', 2); // operational
        } elseif ($status === 'nop') {
            $query->where('vp.VP_OPERATIONNEL', '<', 2);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('vehicule.V_IMMATRICULATION', 'like', "%{$search}%")
                    ->orWhere('vehicule.V_INDICATIF', 'like', "%{$search}%")
                    ->orWhere('vehicule.V_MODELE', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('vehicule.V_INDICATIF')->paginate(30)->withQueryString();

        return view('vehicule.index', compact('items', 'search', 'filtSect', 'status')
            + ['columns' => $this->vehiculeColumns()]);
    }

    private function vehiculeColumns(): array
    {
        $warn = fn (?string $date) => $date && Carbon::parse($date)->lte(now()->addDays(30))
            ? '<i class="fas fa-exclamation-triangle text-warning me-1" title="Expire bientôt"></i>'
            : '';

        return [
            [
                'key' => 'type', 'label' => 'Type', 'type' => 'html',
                'value' => fn ($v) => $v->TV_CODE
                    ? '<i class="'.self::tvIcon($v->TV_CODE).' fa-lg" title="'.e($v->TV_LIBELLE ?? $v->TV_CODE).'"></i>'
                    : '—',
                'exportValue' => fn ($v) => $v->TV_LIBELLE ?? $v->TV_CODE ?? '',
                'mobile' => false, 'default' => true,
            ],
            [
                'key' => 'indicatif', 'label' => 'Indicatif', 'type' => 'text',
                'value' => fn ($v) => $v->V_INDICATIF ?? '—',
                'alwaysVisible' => true, 'sortField' => 'V_INDICATIF', 'mobile' => true,
            ],
            [
                'key' => 'immat', 'label' => 'Immatriculation', 'type' => 'text',
                'value' => fn ($v) => $v->V_IMMATRICULATION ?? '—',
                'alwaysVisible' => true, 'sortField' => 'V_IMMATRICULATION', 'mobile' => true,
            ],
            // Only meaningful with several sites.
            ...(app(FeatureService::class)->isEnabled('multi_site') ? [[
                'key' => 'section', 'label' => 'Section', 'type' => 'text',
                'value' => fn ($v) => $v->section->S_CODE ?? '—',
                'mobile' => false, 'default' => true,
            ]] : []),
            [
                'key' => 'modele', 'label' => 'Modèle', 'type' => 'text',
                'value' => fn ($v) => $v->V_MODELE ?? '—',
                'mobile' => false, 'default' => true,
            ],
            [
                'key' => 'annee', 'label' => 'Année', 'type' => 'text',
                'value' => fn ($v) => $v->V_ANNEE ?? '—',
                'mobile' => false, 'default' => false,
            ],
            [
                'key' => 'statut', 'label' => 'Statut', 'type' => 'badge',
                'value' => fn ($v) => (int) ($v->VP_OPERATIONNEL ?? -999) >= 3 ? '3'
                    : ((int) ($v->VP_OPERATIONNEL ?? -999) >= 1 ? '1' : '0'),
                'badgeMap' => [
                    '3' => ['Opérationnel', 'ob-badge-actif'],
                    '1' => ['Limité',       'ob-badge-ben'],
                    '0' => ['Indisponible', 'ob-badge-bloqued'],
                ],
                'mobile' => true, 'default' => true,
                'exportValue' => fn ($v) => match ((int) ($v->VP_OPERATIONNEL ?? 0)) {
                    2 => 'Opérationnel', 1 => 'Limité', default => 'Indisponible'
                },
            ],
            [
                'key' => 'assurance', 'label' => 'Assurance', 'type' => 'html',
                'value' => fn ($v) => $v->V_ASS_DATE
                    ? $warn($v->V_ASS_DATE).e(Carbon::parse($v->V_ASS_DATE)->format('d/m/Y'))
                    : '—',
                'mobile' => false, 'default' => true,
                'exportValue' => fn ($v) => $v->V_ASS_DATE ? Carbon::parse($v->V_ASS_DATE)->format('d/m/Y') : '',
            ],
            [
                'key' => 'ct', 'label' => 'Contrôle technique', 'type' => 'html',
                'value' => fn ($v) => $v->V_CT_DATE
                    ? $warn($v->V_CT_DATE).e(Carbon::parse($v->V_CT_DATE)->format('d/m/Y'))
                    : '—',
                'mobile' => false, 'default' => true,
                'exportValue' => fn ($v) => $v->V_CT_DATE ? Carbon::parse($v->V_CT_DATE)->format('d/m/Y') : '',
            ],
            [
                'key' => 'revision', 'label' => 'Révision', 'type' => 'html',
                'value' => fn ($v) => $v->V_REV_DATE
                    ? $warn($v->V_REV_DATE).e(Carbon::parse($v->V_REV_DATE)->format('d/m/Y'))
                    : '—',
                'mobile' => false, 'default' => false,
                'exportValue' => fn ($v) => $v->V_REV_DATE ? Carbon::parse($v->V_REV_DATE)->format('d/m/Y') : '',
            ],
            [
                'key' => 'titre', 'label' => "Titre d'accès", 'type' => 'html',
                'value' => fn ($v) => $v->V_TITRE_DATE
                    ? $warn($v->V_TITRE_DATE).e(Carbon::parse($v->V_TITRE_DATE)->format('d/m/Y'))
                    : '—',
                'mobile' => false, 'default' => false,
                'exportValue' => fn ($v) => $v->V_TITRE_DATE ? Carbon::parse($v->V_TITRE_DATE)->format('d/m/Y') : '',
            ],
            [
                'key' => 'neige', 'label' => 'Neige', 'type' => 'html',
                'value' => fn ($v) => $v->V_FLAG1
                    ? '<i class="fas fa-snowflake text-info" title="Équipement neige"></i>'
                    : '—',
                'mobile' => false, 'default' => false,
                'exportValue' => fn ($v) => $v->V_FLAG1 ? 'Oui' : '',
            ],
            [
                'key' => 'clim', 'label' => 'Clim', 'type' => 'html',
                'value' => fn ($v) => $v->V_FLAG2
                    ? '<i class="fas fa-wind text-primary" title="Climatisation"></i>'
                    : '—',
                'mobile' => false, 'default' => false,
                'exportValue' => fn ($v) => $v->V_FLAG2 ? 'Oui' : '',
            ],
            [
                'key' => 'pa', 'label' => 'PA', 'type' => 'html',
                'value' => fn ($v) => $v->V_FLAG3
                    ? '<i class="fas fa-bullhorn text-warning" title="Public Address"></i>'
                    : '—',
                'mobile' => false, 'default' => false,
                'exportValue' => fn ($v) => $v->V_FLAG3 ? 'Oui' : '',
            ],
            [
                'key' => 'att', 'label' => 'Att.', 'type' => 'html',
                'value' => fn ($v) => $v->V_FLAG4
                    ? '<i class="fas fa-link text-secondary" title="Attelage"></i>'
                    : '—',
                'mobile' => false, 'default' => false,
                'exportValue' => fn ($v) => $v->V_FLAG4 ? 'Oui' : '',
            ],
        ];
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function create(): View
    {
        [$types, $positions] = $this->formLookups();

        return view('vehicule.form', [
            'vehicule' => null,
            'types' => $types,
            'positions' => $positions,
            'userSection' => app(SectionScopeService::class)->defaultSectionId(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateVehicule($request);

        $v = Vehicule::create($data);

        return redirect()->route('vehicule.show', $v->V_ID)
            ->with('success', 'Véhicule créé avec succès.');
    }

    // ── Edit / Update ─────────────────────────────────────────────────────────

    public function edit(Vehicule $vehicule): View
    {
        [$types, $positions] = $this->formLookups();

        return view('vehicule.form', [
            'vehicule' => $vehicule,
            'types' => $types,
            'positions' => $positions,
            'userSection' => (int) $vehicule->S_ID,
        ]);
    }

    public function update(Request $request, Vehicule $vehicule): RedirectResponse
    {
        $data = $this->validateVehicule($request);
        $vehicule->update($data);

        return redirect()->route('vehicule.show', $vehicule->V_ID)
            ->with('success', 'Véhicule mis à jour.');
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function destroy(Vehicule $vehicule): RedirectResponse
    {
        DB::transaction(function () use ($vehicule) {
            DB::table('evenement_vehicule')->where('V_ID', $vehicule->V_ID)->delete();
            $vehicule->delete();
        });

        return redirect()->route('vehicule.index')
            ->with('success', 'Véhicule supprimé.');
    }

    // ── Shared helpers ────────────────────────────────────────────────────────

    /** Map TV_CODE → Font Awesome 5 icon class (free tier). */
    private static function tvIcon(string $code): string
    {
        return match ($code) {
            'ASSU', 'VSAV', 'VPS', 'MPS' => 'fas fa-ambulance',
            'CTU', 'VTU', 'VPI' => 'fas fa-truck',
            'ERS' => 'fas fa-ship',
            'GER' => 'fas fa-bolt',
            'MOTO', 'QUAD' => 'fas fa-motorcycle',
            'PCM' => 'fas fa-satellite-dish',
            'REM' => 'fas fa-trailer',
            'VCYN' => 'fas fa-dog',
            'VELO' => 'fas fa-bicycle',
            'VL', 'VLC', 'SSV' => 'fas fa-car',
            'VLHR' => 'fas fa-truck-monster',
            'VSR' => 'fas fa-truck-pickup',
            'VTD' => 'fas fa-hard-hat',
            'VTH' => 'fas fa-bed',
            'VTI' => 'fas fa-boxes',
            'VTP' => 'fas fa-bus',
            default => 'fas fa-car-side',
        };
    }

    private function formLookups(): array
    {
        $types = DB::table('type_vehicule')
            ->orderBy('TV_USAGE')
            ->orderBy('TV_CODE')
            ->get(['TV_CODE', 'TV_LIBELLE', 'TV_USAGE']);

        $positions = DB::table('vehicule_position')
            ->orderByDesc('VP_OPERATIONNEL')
            ->orderBy('VP_LIBELLE')
            ->get(['VP_ID', 'VP_LIBELLE', 'VP_OPERATIONNEL']);

        return [$types, $positions];
    }

    private function validateVehicule(Request $request): array
    {
        // HTML submits "" for empty <select> — pre-convert numeric fields to null/int.
        // VP_ID is varchar('OP','LIM'…) — keep as string, just normalise empty → null.
        $intOrNull = fn (string $key) => $request->filled($key) ? (int) $request->input($key) : null;
        $strOrNull = fn (string $key) => $request->filled($key) ? $request->input($key) : null;

        $request->merge([
            'VP_ID' => $strOrNull('VP_ID'),
            'V_ANNEE' => $intOrNull('V_ANNEE'),
            'V_KM' => $intOrNull('V_KM'),
            'V_KM_REVISION' => $intOrNull('V_KM_REVISION'),
            // V_EXTERNE handled via $request->boolean() — remove from validate to avoid
            // boolean failing when the checkbox is absent from the POST body.
        ]);

        $raw = $request->validate([
            'TV_CODE' => ['required', 'string', 'max:20'],
            'V_IMMATRICULATION' => ['required', 'string', 'max:20'],
            'V_INDICATIF' => ['nullable', 'string', 'max:50'],
            'V_MODELE' => ['nullable', 'string', 'max:50'],
            'V_ANNEE' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'VP_ID' => ['required', 'string', 'max:5'],
            'S_ID' => ['required', 'integer'],
            'V_KM' => ['nullable', 'integer', 'min:0'],
            'V_KM_REVISION' => ['nullable', 'integer', 'min:0'],
            'V_ASS_DATE' => ['nullable', 'date'],
            'V_CT_DATE' => ['nullable', 'date'],
            'V_REV_DATE' => ['nullable', 'date'],
            'V_TITRE_DATE' => ['nullable', 'date'],
            'V_INVENTAIRE' => ['nullable', 'string', 'max:50'],
            'V_COMMENT' => ['nullable', 'string', 'max:2000'],
        ]);

        // Normalise date fields to Y-m-d strings (or null)
        foreach (['V_ASS_DATE', 'V_CT_DATE', 'V_REV_DATE', 'V_TITRE_DATE'] as $f) {
            if (! empty($raw[$f])) {
                try {
                    $raw[$f] = Carbon::parse($raw[$f])->toDateString();
                } catch (\Exception) {
                    $raw[$f] = null;
                }
            }
        }

        // Checkboxes: absent when unchecked, so read with boolean() outside validate()
        $raw['V_EXTERNE'] = $request->boolean('V_EXTERNE') ? 1 : 0;
        $raw['V_FLAG1'] = $request->boolean('V_FLAG1') ? 1 : 0;
        $raw['V_FLAG2'] = $request->boolean('V_FLAG2') ? 1 : 0;
        $raw['V_FLAG3'] = $request->boolean('V_FLAG3') ? 1 : 0;
        $raw['V_FLAG4'] = $request->boolean('V_FLAG4') ? 1 : 0;

        // Enforce section isolation: a vehicle can only be attached to a
        // section inside the editor's visible scope.
        $raw['S_ID'] = app(SectionScopeService::class)->coerce((int) $raw['S_ID']);

        return $raw;
    }

    public function show(Vehicule $vehicule): View
    {
        $vehicule->load(['section']);

        // Position / operational status
        $position = DB::table('vehicule_position')
            ->where('VP_ID', $vehicule->VP_ID)
            ->first();

        // Vehicle type details (for icon tooltip on show page)
        $typeVehicule = DB::table('type_vehicule')
            ->where('TV_CODE', $vehicule->TV_CODE)
            ->first();

        // Last 10 events this vehicle was on
        $recentEvents = DB::table('evenement_vehicule as ev')
            ->join('evenement as e', 'ev.E_CODE', '=', 'e.E_CODE')
            ->join('evenement_horaire as eh', function ($j) {
                $j->on('eh.E_CODE', '=', 'ev.E_CODE')
                    ->on('eh.EH_ID', '=', 'ev.EH_ID');
            })
            ->where('ev.V_ID', $vehicule->V_ID)
            ->orderByDesc('eh.EH_DATE_DEBUT')
            ->limit(10)
            ->select('e.E_CODE', 'e.E_LIBELLE', 'eh.EH_DATE_DEBUT', 'ev.EV_KM')
            ->get();

        // Materials assigned to this vehicle
        $materiels = DB::table('materiel as m')
            ->leftJoin('type_materiel as tm', 'm.TM_ID', '=', 'tm.TM_ID')
            ->where('m.V_ID', $vehicule->V_ID)
            ->orderBy('tm.TM_DESCRIPTION')
            ->orderBy('m.MA_MODELE')
            ->select(
                'm.MA_ID', 'm.MA_MODELE', 'm.MA_NUMERO_SERIE',
                'm.MA_NB', 'm.MA_INVENTAIRE', 'm.MA_LIEU_STOCKAGE',
                'tm.TM_DESCRIPTION', 'tm.TM_CODE'
            )
            ->get();

        // Documents linked to this vehicle
        $documents = DB::table('document as d')
            ->leftJoin('type_document as td', 'd.TD_CODE', '=', 'td.TD_CODE')
            ->where('d.V_ID', $vehicule->V_ID)
            ->orderByDesc('d.D_CREATED_DATE')
            ->select('d.D_ID', 'd.D_NAME', 'd.D_CREATED_DATE', 'td.TD_LIBELLE')
            ->get();

        return view('vehicule.show', compact(
            'vehicule', 'position', 'typeVehicule',
            'recentEvents', 'materiels', 'documents'
        ));
    }
}
