<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RemplacementController extends Controller
{
    public function index(Request $request): View
    {
        $user      = auth()->user();
        $pid       = (int) $user->P_ID;
        $sectionId = (int) $user->P_SECTION;

        $tab = (string) $request->string('tab', 'mine'); // mine | section

        $query = DB::table('remplacement as r')
            ->join('pompier as p1', 'r.REPLACED', '=', 'p1.P_ID')
            ->join('pompier as p2', 'r.SUBSTITUTE', '=', 'p2.P_ID')
            ->join('evenement as e', 'r.E_CODE', '=', 'e.E_CODE')
            ->join('evenement_horaire as eh', function ($j) {
                $j->on('eh.E_CODE', '=', 'r.E_CODE')
                  ->on('eh.EH_ID',  '=', 'r.EH_ID');
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

        return view('remplacement.index', compact('items', 'tab'));
    }
}
