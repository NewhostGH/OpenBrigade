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
                'vehicule.V_ID', 'vehicule.V_IMMAT', 'vehicule.V_LIBELLE',
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
                $q->where('vehicule.V_IMMAT', 'like', "%{$search}%")
                  ->orWhere('vehicule.V_LIBELLE', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('vehicule.V_LIBELLE')->paginate(30)->withQueryString();

        $sections = Section::query()->orderBy('S_CODE')->get(['S_ID', 'S_CODE', 'S_DESCRIPTION']);

        return view('vehicule.index', compact('items', 'search', 'filtSect', 'status', 'sections'));
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
