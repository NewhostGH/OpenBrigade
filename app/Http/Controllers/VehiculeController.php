<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Vehicule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VehiculeController extends Controller
{
    public function index(Request $request): View
    {
        $user      = auth()->user();
        $sectionId = (int) $user->P_SECTION;

        $search    = trim((string) $request->string('q'));
        $filtSect  = (int) $request->integer('section', 0);
        $status    = (string) $request->string('status', 'all');

        $query = Vehicule::query()
            ->with(['section'])
            ->leftJoin('vehicule_position as vp', 'vp.VP_ID', '=', 'vehicule.VP_ID')
            ->select(
                'vehicule.V_ID', 'vehicule.V_IMMATRICULATION', 'vehicule.V_INDICATIF',
                'vehicule.TV_CODE', 'vehicule.V_MODELE', 'vehicule.V_ANNEE',
                'vehicule.S_ID', 'vehicule.V_EXTERNE',
                'vehicule.V_ASS_DATE', 'vehicule.V_CT_DATE',
                'vehicule.V_REV_DATE', 'vehicule.V_TITRE_DATE',
                'vp.VP_LIBELLE', 'vp.VP_OPERATIONNEL'
            );

        // Section filter
        $targetSection = $filtSect > 0 ? $filtSect : $sectionId;
        $query->where('vehicule.S_ID', $targetSection);

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

        $sections = Section::query()->orderBy('S_CODE')->get(['S_ID', 'S_CODE', 'S_DESCRIPTION']);

        return view('vehicule.index', compact('items', 'search', 'filtSect', 'status', 'sections')
            + ['columns' => $this->vehiculeColumns()]);
    }

    private function vehiculeColumns(): array
    {
        $warn = fn(?string $date) => $date && \Carbon\Carbon::parse($date)->lte(now()->addDays(30))
            ? '<i class="fas fa-exclamation-triangle text-warning me-1" title="Expire bientôt"></i>'
            : '';

        return [
            [
                'key' => 'indicatif', 'label' => 'Indicatif', 'type' => 'text',
                'value' => fn($v) => $v->V_INDICATIF ?? '—',
                'alwaysVisible' => true, 'sortField' => 'V_INDICATIF', 'mobile' => true,
            ],
            [
                'key' => 'immat', 'label' => 'Immatriculation', 'type' => 'text',
                'value' => fn($v) => $v->V_IMMATRICULATION ?? '—',
                'alwaysVisible' => true, 'sortField' => 'V_IMMATRICULATION', 'mobile' => true,
            ],
            [
                'key' => 'modele', 'label' => 'Modèle', 'type' => 'text',
                'value' => fn($v) => trim(($v->TV_CODE ?? '') . ' ' . ($v->V_MODELE ?? '')),
                'mobile' => false, 'default' => true,
            ],
            [
                'key' => 'annee', 'label' => 'Année', 'type' => 'text',
                'value' => fn($v) => $v->V_ANNEE ?? '—',
                'mobile' => false, 'default' => false,
            ],
            [
                'key' => 'statut', 'label' => 'Statut', 'type' => 'badge',
                'value' => fn($v) => match((int)($v->VP_OPERATIONNEL ?? 0)) {
                    2 => '2', 1 => '1', default => '0'
                },
                'badgeMap' => [
                    '2' => ['Opérationnel', 'ob-badge-actif'],
                    '1' => ['Limité',       'ob-badge-ben'],
                    '0' => ['Indisponible', 'ob-badge-bloqued'],
                ],
                'mobile' => true, 'default' => true,
                'exportValue' => fn($v) => match((int)($v->VP_OPERATIONNEL ?? 0)) {
                    2 => 'Opérationnel', 1 => 'Limité', default => 'Indisponible'
                },
            ],
            [
                'key' => 'assurance', 'label' => 'Assurance', 'type' => 'html',
                'value' => fn($v) => $v->V_ASS_DATE
                    ? $warn($v->V_ASS_DATE) . e(\Carbon\Carbon::parse($v->V_ASS_DATE)->format('d/m/Y'))
                    : '—',
                'mobile' => false, 'default' => true,
                'exportValue' => fn($v) => $v->V_ASS_DATE ? \Carbon\Carbon::parse($v->V_ASS_DATE)->format('d/m/Y') : '',
            ],
            [
                'key' => 'ct', 'label' => 'Contrôle technique', 'type' => 'html',
                'value' => fn($v) => $v->V_CT_DATE
                    ? $warn($v->V_CT_DATE) . e(\Carbon\Carbon::parse($v->V_CT_DATE)->format('d/m/Y'))
                    : '—',
                'mobile' => false, 'default' => true,
                'exportValue' => fn($v) => $v->V_CT_DATE ? \Carbon\Carbon::parse($v->V_CT_DATE)->format('d/m/Y') : '',
            ],
            [
                'key' => 'revision', 'label' => 'Révision', 'type' => 'html',
                'value' => fn($v) => $v->V_REV_DATE
                    ? $warn($v->V_REV_DATE) . e(\Carbon\Carbon::parse($v->V_REV_DATE)->format('d/m/Y'))
                    : '—',
                'mobile' => false, 'default' => false,
                'exportValue' => fn($v) => $v->V_REV_DATE ? \Carbon\Carbon::parse($v->V_REV_DATE)->format('d/m/Y') : '',
            ],
            [
                'key' => 'titre', 'label' => 'Carte grise', 'type' => 'html',
                'value' => fn($v) => $v->V_TITRE_DATE
                    ? $warn($v->V_TITRE_DATE) . e(\Carbon\Carbon::parse($v->V_TITRE_DATE)->format('d/m/Y'))
                    : '—',
                'mobile' => false, 'default' => false,
                'exportValue' => fn($v) => $v->V_TITRE_DATE ? \Carbon\Carbon::parse($v->V_TITRE_DATE)->format('d/m/Y') : '',
            ],
        ];
    }

    public function show(Vehicule $vehicule): View
    {
        $vehicule->load(['section']);

        // Position / operational status
        $position = DB::table('vehicule_position')
            ->where('VP_ID', $vehicule->VP_ID)
            ->first();

        // Last 10 events this vehicle was on
        $recentEvents = DB::table('evenement_vehicule as ev')
            ->join('evenement as e', 'ev.E_CODE', '=', 'e.E_CODE')
            ->join('evenement_horaire as eh', function ($j) {
                $j->on('eh.E_CODE', '=', 'ev.E_CODE')
                  ->on('eh.EH_ID',  '=', 'ev.EH_ID');
            })
            ->where('ev.V_ID', $vehicule->V_ID)
            ->orderByDesc('eh.EH_DATE_DEBUT')
            ->limit(10)
            ->select('e.E_CODE', 'e.E_LIBELLE', 'eh.EH_DATE_DEBUT', 'ev.EV_KM')
            ->get();

        return view('vehicule.show', compact('vehicule', 'position', 'recentEvents'));
    }
}
