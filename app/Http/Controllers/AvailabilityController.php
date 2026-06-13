<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AvailabilityController extends Controller
{
    /**
     * Personal availability — shows the user's dispo/indispo for the coming weeks.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $pid = (int) $user->P_ID;

        $weeksAhead = 4;
        $today = now()->startOfDay();
        $until = $today->copy()->addWeeks($weeksAhead);

        // Periods for the next 4 weeks
        $periods = DB::table('disponibilite_periode')
            ->orderBy('DP_ID')
            ->get(['DP_ID', 'DP_NAME', 'DP_DEBUT', 'DP_FIN']);

        // User's disponibilités for the range
        $dispos = DB::table('disponibilite')
            ->where('P_ID', $pid)
            ->whereBetween('D_DATE', [$today->toDateString(), $until->toDateString()])
            ->pluck('PERIOD_ID', 'D_DATE')
            ->toArray();

        // Build week grid
        $weeks = [];
        $cursor = $today->copy()->startOfWeek();
        for ($w = 0; $w < $weeksAhead; $w++) {
            $week = [];
            for ($d = 0; $d < 7; $d++) {
                $date = $cursor->copy()->addDays($d);
                $dateKey = $date->format('Y-m-d');
                $week[] = [
                    'date' => $date,
                    'key' => $dateKey,
                    'isToday' => $date->isToday(),
                    'periodId' => $dispos[$dateKey] ?? null,
                ];
            }
            $weeks[] = $week;
            $cursor->addWeek();
        }

        // User's upcoming absences (indisponibilités)
        $absences = DB::table('indisponibilite as i')
            ->leftJoin('type_indisponibilite as ti', 'i.TI_CODE', '=', 'ti.TI_CODE')
            ->where('i.P_ID', $pid)
            ->where('i.I_CANCEL', 0)
            ->where('i.I_FIN', '>=', $today->toDateString())
            ->orderBy('i.I_DEBUT')
            ->select('i.I_CODE', 'i.I_DEBUT', 'i.I_FIN', 'i.I_ACCEPT', 'ti.TI_LIBELLE')
            ->get();

        return view('availability.index', compact('weeks', 'periods', 'absences'));
    }
}
