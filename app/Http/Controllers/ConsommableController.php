<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ConsommableController extends Controller
{
    public function index(Request $request): View
    {
        $user      = auth()->user();
        $sectionId = (int) $user->P_SECTION;

        $search   = trim((string) $request->string('q'));
        $filtSect = (int) $request->integer('section', 0);
        $alert    = (bool) $request->boolean('alert', false);

        $target = $filtSect > 0 ? $filtSect : $sectionId;

        $today = now()->toDateString();

        $query = DB::table('consommable as c')
            ->leftJoin('type_consommable as tc', 'c.TC_ID', '=', 'tc.TC_ID')
            ->where('c.S_ID', $target)
            ->select(
                'c.C_ID', 'c.C_DESCRIPTION', 'c.C_NOMBRE',
                'c.C_MINIMUM', 'c.C_DATE_PEREMPTION', 'c.C_LIEU_STOCKAGE',
                'tc.TC_LIBELLE',
                DB::raw("CASE
                    WHEN c.C_DATE_PEREMPTION IS NOT NULL AND c.C_DATE_PEREMPTION < '{$today}' THEN 'expired'
                    WHEN c.C_DATE_PEREMPTION IS NOT NULL AND c.C_DATE_PEREMPTION <= DATE_ADD('{$today}', INTERVAL 90 DAY) THEN 'expiring'
                    WHEN c.C_MINIMUM > 0 AND c.C_NOMBRE < c.C_MINIMUM THEN 'low'
                    ELSE 'ok'
                END as alert_level")
            )
            ->orderBy('tc.TC_LIBELLE')
            ->orderBy('c.C_DESCRIPTION');

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('c.C_DESCRIPTION', 'like', "%{$search}%")
                  ->orWhere('tc.TC_LIBELLE', 'like', "%{$search}%");
            });
        }

        if ($alert) {
            $query->where(function ($q) use ($today): void {
                $q->whereRaw("(c.C_DATE_PEREMPTION IS NOT NULL AND c.C_DATE_PEREMPTION <= DATE_ADD('{$today}', INTERVAL 90 DAY))")
                  ->orWhereRaw('(c.C_MINIMUM > 0 AND c.C_NOMBRE < c.C_MINIMUM)');
            });
        }

        $items    = $query->paginate(50)->withQueryString();
        $sections = Section::query()->orderBy('S_CODE')->get(['S_ID', 'S_CODE', 'S_DESCRIPTION']);

        return view('consommable.index', compact('items', 'search', 'filtSect', 'alert', 'sections'));
    }
}
