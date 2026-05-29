<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MaterielController extends Controller
{
    public function index(Request $request): View
    {
        $user      = auth()->user();
        $sectionId = (int) $user->P_SECTION;

        $search   = trim((string) $request->string('q'));
        $filtSect = (int) $request->integer('section', 0);

        $target = $filtSect > 0 ? $filtSect : $sectionId;

        $query = DB::table('materiel as m')
            ->leftJoin('type_materiel as tm', 'm.TM_ID', '=', 'tm.TM_ID')
            ->leftJoin('section as s', 'm.S_ID', '=', 's.S_ID')
            ->where('m.S_ID', $target)
            ->select(
                'm.MA_ID', 'm.MA_MODELE', 'm.MA_NUMERO_SERIE',
                'm.MA_LIEU_STOCKAGE', 'm.MA_REV_DATE', 'm.MA_NB',
                'tm.TM_LIBELLE', 'tm.TM_USAGE'
            )
            ->orderBy('tm.TM_LIBELLE')
            ->orderBy('m.MA_MODELE');

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('m.MA_MODELE', 'like', "%{$search}%")
                  ->orWhere('m.MA_NUMERO_SERIE', 'like', "%{$search}%")
                  ->orWhere('tm.TM_LIBELLE', 'like', "%{$search}%");
            });
        }

        $items    = $query->paginate(50)->withQueryString();
        $sections = Section::query()->orderBy('S_CODE')->get(['S_ID', 'S_CODE', 'S_DESCRIPTION']);

        return view('materiel.index', compact('items', 'search', 'filtSect', 'sections'));
    }
}
