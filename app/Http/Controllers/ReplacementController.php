<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReplacementController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $pid = (int) $user->P_ID;
        $sectionId = (int) $user->P_SECTION;

        $tab = (string) $request->string('tab', 'mine'); // mine | section

        $query = DB::table('remplacement as r')
            ->join('pompier as p1', 'r.REPLACED', '=', 'p1.P_ID')
            ->join('pompier as p2', 'r.SUBSTITUTE', '=', 'p2.P_ID')
            ->join('evenement as e', 'r.E_CODE', '=', 'e.E_CODE')
            ->join('evenement_horaire as eh', function ($j) {
                $j->on('eh.E_CODE', '=', 'r.E_CODE')
                    ->on('eh.EH_ID', '=', 'r.EH_ID');
            })
            ->select(
                'r.R_ID', 'r.ACCEPTED', 'r.APPROVED', 'r.REJECTED',
                'r.REQUEST_DATE',
                'e.E_CODE', 'e.E_LIBELLE',
                'eh.EH_DATE_DEBUT',
                DB::raw("CONCAT(p1.P_PRENOM, ' ', p1.P_NOM) as replaced_name"),
                DB::raw("CONCAT(p2.P_PRENOM, ' ', p2.P_NOM) as substitute_name")
            )
            ->orderByDesc('r.REQUEST_DATE');

        if ($tab === 'section') {
            $query->where(function ($q) use ($sectionId) {
                $q->where('p1.P_SECTION', $sectionId)
                    ->orWhere('p2.P_SECTION', $sectionId);
            });
        } else {
            // Show replacements where the user is involved
            $query->where(function ($q) use ($pid) {
                $q->where('r.REPLACED', $pid)
                    ->orWhere('r.SUBSTITUTE', $pid);
            });
        }

        $items = $query->paginate(30)->withQueryString();

        return view('replacement.index', compact('items', 'tab')
            + ['columns' => $this->remplacementColumns()]);
    }

    private function remplacementColumns(): array
    {
        return [
            ['key' => 'activite', 'label' => 'Activité', 'type' => 'html', 'value' => fn ($r) => '<a href="'.route('event.show', $r->E_CODE).'" class="text-decoration-none">'.e($r->E_LIBELLE ?? $r->E_CODE).'</a>', 'alwaysVisible' => true, 'mobile' => true, 'exportable' => true, 'exportValue' => fn ($r) => $r->E_LIBELLE ?? $r->E_CODE],
            ['key' => 'date', 'label' => 'Date', 'type' => 'html', 'value' => fn ($r) => $r->EH_DATE_DEBUT ? Carbon::parse($r->EH_DATE_DEBUT)->locale('fr')->isoFormat('ddd D MMM YYYY') : '—', 'mobile' => false, 'exportable' => true, 'exportValue' => fn ($r) => $r->EH_DATE_DEBUT ? Carbon::parse($r->EH_DATE_DEBUT)->format('d/m/Y') : ''],
            ['key' => 'remplace', 'label' => 'Remplacé', 'type' => 'text', 'value' => fn ($r) => $r->replaced_name, 'alwaysVisible' => true, 'mobile' => true, 'exportable' => true, 'exportValue' => fn ($r) => $r->replaced_name],
            ['key' => 'remplacant', 'label' => 'Remplaçant', 'type' => 'text', 'value' => fn ($r) => $r->substitute_name, 'mobile' => false, 'exportable' => true, 'exportValue' => fn ($r) => $r->substitute_name],
            ['key' => 'statut', 'label' => 'Statut', 'type' => 'badge', 'value' => fn ($r) => $r->APPROVED ? 'APPROVED' : ($r->REJECTED ? 'REJECTED' : ($r->ACCEPTED ? 'ACCEPTED' : 'PENDING')), 'badgeMap' => ['APPROVED' => ['Approuvé', 'ob-badge-actif'], 'REJECTED' => ['Refusé', 'ob-badge-bloqued'], 'ACCEPTED' => ['Accepté', 'ob-badge-int'], 'PENDING' => ['En attente', 'ob-badge-ben']], 'exportable' => true, 'exportValue' => fn ($r) => $r->APPROVED ? 'Approuvé' : ($r->REJECTED ? 'Refusé' : ($r->ACCEPTED ? 'Accepté' : 'En attente')), 'mobile' => true],
        ];
    }
}
