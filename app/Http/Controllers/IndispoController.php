<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class IndispoController extends Controller
{
    /**
     * Availability / absences list for the section.
     */
    public function index(Request $request): View
    {
        $user      = auth()->user();
        $sectionId = (int) $user->P_SECTION;
        $pid       = (int) $user->P_ID;

        $tab    = (string) $request->string('tab', 'section'); // section | mine
        $status = (string) $request->string('status', 'pending'); // pending | accepted | all

        $query = DB::table('indisponibilite as i')
            ->leftJoin('pompier as p', 'i.P_ID', '=', 'p.P_ID')
            ->leftJoin('type_indisponibilite as ti', 'i.TI_CODE', '=', 'ti.TI_CODE')
            ->where('i.I_CANCEL', 0)
            ->select(
                'i.I_CODE', 'i.P_ID', 'i.I_DEBUT', 'i.I_FIN',
                'i.I_ACCEPT', 'i.I_COMMENT', 'i.I_JOUR_COMPLET',
                'ti.TI_LIBELLE',
                DB::raw("CONCAT(p.P_PRENOM, ' ', p.P_NOM) as person_name")
            )
            ->orderByDesc('i.I_DEBUT');

        if ($tab === 'mine') {
            $query->where('i.P_ID', $pid);
        } else {
            $query->where('p.P_SECTION', $sectionId);
        }

        match ($status) {
            'pending'  => $query->whereNull('i.I_ACCEPT')->orWhere('i.I_ACCEPT', 0),
            'accepted' => $query->where('i.I_ACCEPT', 1),
            default    => null,
        };

        // Add upcoming filter: only show current/future
        $query->where(function ($q) {
            $q->where('i.I_FIN', '>=', now()->toDateString())
              ->orWhereNull('i.I_FIN');
        });

        $items = $query->paginate(30)->withQueryString();

        return view('indispo.index', compact('items', 'tab', 'status')
            + ['columns' => $this->indispoColumns()]);
    }

    private function indispoColumns(): array
    {
        return [
            ['key'=>'personnel','label'=>'Personnel','type'=>'text','value'=>fn($i)=>$i->person_name ?? '—','alwaysVisible'=>true,'mobile'=>true],
            ['key'=>'type','label'=>'Type','type'=>'text','value'=>fn($i)=>$i->TI_LIBELLE ?? '—','mobile'=>false,'exportable'=>true,'exportValue'=>fn($i)=>$i->TI_LIBELLE ?? ''],
            ['key'=>'debut','label'=>'Début','type'=>'date','value'=>fn($i)=>$i->I_DEBUT,'alwaysVisible'=>true,'mobile'=>true,'exportable'=>true,'exportValue'=>fn($i)=>$i->I_DEBUT?\Carbon\Carbon::parse($i->I_DEBUT)->format('d/m/Y'):''],
            ['key'=>'fin','label'=>'Fin','type'=>'date','value'=>fn($i)=>$i->I_FIN,'mobile'=>false,'exportable'=>true,'exportValue'=>fn($i)=>$i->I_FIN?\Carbon\Carbon::parse($i->I_FIN)->format('d/m/Y'):''],
            ['key'=>'statut','label'=>'Statut','type'=>'badge','value'=>fn($i)=>$i->I_ACCEPT == 1 ? 'ACCEPTED' : ($i->I_ACCEPT === null || $i->I_ACCEPT == 0 ? 'PENDING' : 'REJECTED'),'badgeMap'=>['ACCEPTED'=>['Acceptée','ob-badge-actif'],'REJECTED'=>['Refusée','ob-badge-bloqued'],'PENDING'=>['En attente','ob-badge-ben']],'exportable'=>true,'exportValue'=>fn($i)=>$i->I_ACCEPT == 1 ? 'Acceptée' : ($i->I_ACCEPT === null || $i->I_ACCEPT == 0 ? 'En attente' : 'Refusée'),'mobile'=>true],
            ['key'=>'commentaire','label'=>'Commentaire','type'=>'text','value'=>fn($i)=>$i->I_COMMENT ?: '','mobile'=>false,'default'=>false,'exportable'=>true,'exportValue'=>fn($i)=>$i->I_COMMENT ?? ''],
        ];
    }
}
