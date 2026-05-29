<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GardeController extends Controller
{
    /**
     * On-call roster (tableau de garde) for a given week.
     *
     * Displays all astreinte slots for the user's section within the
     * selected week, grouped by day, so operators can see who is on duty.
     */
    public function index(Request $request): View
    {
        $user      = auth()->user();
        $sectionId = (int) $user->P_SECTION;

        // Week navigation: default to current week's Monday
        $weekOffset = (int) $request->integer('week', 0);
        $monday  = now()->startOfWeek()->addWeeks($weekOffset);
        $sunday  = $monday->copy()->endOfWeek();

        $prevWeek = $weekOffset - 1;
        $nextWeek = $weekOffset + 1;

        // Fetch all slots for the week
        $slots = DB::table('astreinte as a')
            ->join('pompier as p', 'a.P_ID', '=', 'p.P_ID')
            ->join('groupe as g', 'a.GP_ID', '=', 'g.GP_ID')
            ->where('a.S_ID', $sectionId)
            ->whereBetween('a.AS_DEBUT', [
                $monday->toDateTimeString(),
                $sunday->toDateTimeString(),
            ])
            ->orderBy('a.AS_DEBUT')
            ->orderBy('p.P_NOM')
            ->select(
                'a.AS_ID',
                'a.AS_DEBUT',
                'a.AS_FIN',
                'p.P_ID',
                'p.P_NOM',
                'p.P_PRENOM',
                'p.P_PHOTO',
                'p.P_PHONE',
                'g.GP_DESCRIPTION',
                DB::raw("DATE(a.AS_DEBUT) as day_date")
            )
            ->get();

        // Group slots by day
        $byDay = $slots->groupBy('day_date');

        // Build the 7-day grid
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $date     = $monday->copy()->addDays($i);
            $dateKey  = $date->format('Y-m-d');
            $days[] = [
                'date'    => $date,
                'label'   => ucfirst($date->locale('fr')->isoFormat('ddd D MMM')),
                'slots'   => $byDay->get($dateKey, collect()),
                'isToday' => $date->isToday(),
            ];
        }

        // Roles for column headings
        $roles = DB::table('astreinte as a')
            ->join('groupe as g', 'a.GP_ID', '=', 'g.GP_ID')
            ->where('a.S_ID', $sectionId)
            ->whereBetween('a.AS_DEBUT', [
                $monday->toDateTimeString(),
                $sunday->toDateTimeString(),
            ])
            ->distinct()
            ->orderBy('g.GP_DESCRIPTION')
            ->pluck('g.GP_DESCRIPTION')
            ->toArray();

        return view('garde.index', compact(
            'days', 'monday', 'sunday', 'prevWeek', 'nextWeek', 'weekOffset', 'roles'
        ));
    }
}
