<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EvenementController extends Controller
{
    // ── Event list ────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $user      = auth()->user();
        $sectionId = (int) $user->P_SECTION;

        $period   = (string) $request->string('period', 'upcoming');
        $search   = trim((string) $request->string('q'));
        $type     = (string) $request->string('type', 'ALL');
        $filtSect = (int) $request->integer('section', 0);

        $query = Evenement::query()
            ->with(['horaires', 'section'])
            ->join('evenement_horaire as eh', 'evenement.E_CODE', '=', 'eh.E_CODE')
            ->join('type_evenement as te', 'evenement.TE_CODE', '=', 'te.TE_CODE')
            ->select([
                'evenement.E_CODE',
                'evenement.E_LIBELLE',
                'evenement.E_LIEU',
                'evenement.E_CLOSED',
                'evenement.E_CANCELED',
                'evenement.E_NB',
                'evenement.S_ID',
                'evenement.TE_CODE',
                'evenement.E_EQUIPE',
                'te.TE_LIBELLE',
                'te.TE_ICON',
                DB::raw("MIN(eh.EH_DATE_DEBUT) as first_date"),
                DB::raw("MIN(eh.EH_DEBUT) as first_time"),
            ])
            ->groupBy(
                'evenement.E_CODE', 'evenement.E_LIBELLE', 'evenement.E_LIEU',
                'evenement.E_CLOSED', 'evenement.E_CANCELED', 'evenement.E_NB',
                'evenement.S_ID', 'evenement.TE_CODE', 'evenement.E_EQUIPE',
                'te.TE_LIBELLE', 'te.TE_ICON'
            )
            ->where('evenement.E_CANCELED', 0)
            ->where('evenement.E_EQUIPE', 0);

        match ($period) {
            'past'     => $query->having(DB::raw("MIN(eh.EH_DATE_DEBUT)"), '<', now()->toDateString()),
            'upcoming' => $query->having(DB::raw("MAX(eh.EH_DATE_FIN)"), '>=', now()->toDateString()),
            default    => null, // 'all'
        };

        if ($type !== '' && $type !== 'ALL') {
            $query->where('evenement.TE_CODE', $type);
        }

        if ($filtSect > 0) {
            $query->where('evenement.S_ID', $filtSect);
        } else {
            $query->where('evenement.S_ID', $sectionId);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('evenement.E_LIBELLE', 'like', "%{$search}%")
                  ->orWhere('evenement.E_LIEU', 'like', "%{$search}%")
                  ->orWhere('evenement.E_CODE', 'like', "%{$search}%");
            });
        }

        if ($period === 'past') {
            $query->orderByDesc('first_date');
        } else {
            $query->orderBy('first_date');
        }

        $items = $query->paginate(50)->withQueryString();

        // Event types for the filter dropdown
        $types = DB::table('type_evenement')
            ->where('TE_CODE', '<>', 'MC')
            ->orderBy('TE_LIBELLE')
            ->get(['TE_CODE', 'TE_LIBELLE']);

        $sections = Section::query()->orderBy('S_CODE')->get(['S_ID', 'S_CODE', 'S_DESCRIPTION']);

        return view('evenement.index', compact(
            'items', 'period', 'search', 'type', 'filtSect', 'types', 'sections'
        ) + ['columns' => $this->evenementColumns()]);
    }

    private function evenementColumns(): array
    {
        return [
            ['key'=>'icon','label'=>'','type'=>'html','value'=>fn($e) => '<i class="fas fa-'.($e->TE_ICON ?? 'calendar').'" style="color:var(--text-muted-soft)" title="'.e($e->TE_LIBELLE ?? '').'"></i>','alwaysVisible'=>true,'exportable'=>false,'mobile'=>true],
            ['key'=>'activite','label'=>'Activité','type'=>'html','value'=>fn($e)=>'<a href="'.route('evenement.show',$e->E_CODE).'" class="text-decoration-none fw-semibold">'.e($e->E_LIBELLE ?? $e->E_CODE).'</a>','alwaysVisible'=>true,'exportable'=>true,'exportValue'=>fn($e)=>$e->E_LIBELLE ?? $e->E_CODE,'sortField'=>'E_INTITULE','mobile'=>true],
            ['key'=>'lieu','label'=>'Lieu','type'=>'text','value'=>fn($e)=>$e->E_LIEU ?? '—','mobile'=>false,'exportable'=>true,'exportValue'=>fn($e)=>$e->E_LIEU ?? ''],
            ['key'=>'date','label'=>'Date','type'=>'html','value'=>fn($e)=>$e->first_date ? \Carbon\Carbon::parse($e->first_date)->locale('fr')->isoFormat('ddd D MMM YYYY').($e->first_time ? ' <span class="text-muted">'.substr($e->first_time,0,5).'</span>' : '') : '—','mobile'=>false,'exportable'=>true,'exportValue'=>fn($e)=>$e->first_date?\Carbon\Carbon::parse($e->first_date)->format('d/m/Y'):''],
            ['key'=>'statut','label'=>'Statut','type'=>'badge','value'=>fn($e)=>$e->E_CANCELED ? 'ANNULEE' : ($e->E_CLOSED ? 'CLOSE' : 'OPEN'),'badgeMap'=>['ANNULEE'=>['Annulée','ob-badge-bloqued'],'CLOSE'=>['Clôturée','ob-badge-archive'],'OPEN'=>['Ouverte','ob-badge-actif']],'exportable'=>true,'exportValue'=>fn($e)=>$e->E_CANCELED ? 'Annulée' : ($e->E_CLOSED ? 'Clôturée' : 'Ouverte'),'mobile'=>false],
        ];
    }

    // ── Event detail ──────────────────────────────────────────────────────────

    public function show(Request $request, string $code): View
    {
        $event = Evenement::with([
            'section',
            'horaires',
            'chef',
        ])->findOrFail($code);

        $tab = (string) $request->string('tab', 'personnel');

        // Participants grouped by horaire slot
        $participants = [];
        if ($tab === 'personnel') {
            $participants = DB::table('evenement_participation as ep')
                ->join('pompier as p', 'ep.P_ID', '=', 'p.P_ID')
                ->join('evenement_horaire as eh', function ($j) use ($code) {
                    $j->on('eh.EH_ID', '=', 'ep.EH_ID')
                      ->where('eh.E_CODE', '=', $code);
                })
                ->where('ep.E_CODE', $code)
                ->where('ep.EP_ABSENT', 0)
                ->orderBy('p.P_NOM')
                ->orderBy('p.P_PRENOM')
                ->select(
                    'p.P_ID', 'p.P_NOM', 'p.P_PRENOM', 'p.P_PHOTO',
                    'p.P_GRADE', 'p.P_STATUT',
                    'eh.EH_DATE_DEBUT', 'eh.EH_DEBUT', 'eh.EH_FIN',
                    'ep.EP_COMMENT', 'ep.TP_ID'
                )
                ->get();
        }

        $vehicules = [];
        if ($tab === 'vehicule') {
            $vehicules = DB::table('evenement_vehicule as ev')
                ->join('vehicule as v', 'ev.V_ID', '=', 'v.V_ID')
                ->where('ev.E_CODE', $code)
                ->select('v.V_ID', 'v.V_IMMAT', 'v.V_LIBELLE', 'ev.EV_KM')
                ->get();
        }

        return view('evenement.show', compact('event', 'tab', 'participants', 'vehicules'));
    }
}
