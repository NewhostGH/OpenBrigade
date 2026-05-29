<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PlanningController extends Controller
{
    /**
     * Personal agenda — events the user is signed up for + their absences,
     * displayed in a month grid.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $pid  = (int) $user->P_ID;

        // Month navigation
        $year  = (int) $request->integer('year',  now()->year);
        $month = (int) $request->integer('month', now()->month);

        // Clamp month
        if ($month < 1)  { $month = 12; $year--; }
        if ($month > 12) { $month = 1;  $year++; }

        $first = Carbon::create($year, $month, 1)->startOfDay();
        $last  = $first->copy()->endOfMonth();

        $prevYear  = $month === 1  ? $year - 1 : $year;
        $prevMonth = $month === 1  ? 12         : $month - 1;
        $nextYear  = $month === 12 ? $year + 1  : $year;
        $nextMonth = $month === 12 ? 1           : $month + 1;

        // ── Events the user is signed up for ─────────────────────────────────
        $events = DB::table('evenement_participation as ep')
            ->join('evenement as e', 'ep.E_CODE', '=', 'e.E_CODE')
            ->join('evenement_horaire as eh', function ($j) {
                $j->on('eh.E_CODE', '=', 'ep.E_CODE')
                  ->on('eh.EH_ID',  '=', 'ep.EH_ID');
            })
            ->join('type_evenement as te', 'e.TE_CODE', '=', 'te.TE_CODE')
            ->where('ep.P_ID', $pid)
            ->where('ep.EP_ABSENT', 0)
            ->where('e.E_CANCELED', 0)
            ->whereBetween('eh.EH_DATE_DEBUT', [
                $first->toDateString(),
                $last->toDateString(),
            ])
            ->select(
                'e.E_CODE',
                'e.E_LIBELLE',
                'e.E_CLOSED',
                'te.TE_ICON',
                'te.TE_LIBELLE',
                DB::raw("DATE(eh.EH_DATE_DEBUT) as event_date"),
                DB::raw("TIME_FORMAT(eh.EH_DEBUT,'%H:%i') as event_time")
            )
            ->orderBy('eh.EH_DATE_DEBUT')
            ->get();

        // ── User absences / indisponibilités ──────────────────────────────────
        $absences = DB::table('indisponibilite as i')
            ->leftJoin('type_indisponibilite as ti', 'i.TI_CODE', '=', 'ti.TI_CODE')
            ->where('i.P_ID', $pid)
            ->where('i.I_CANCEL', 0)
            ->where(function ($q) use ($first, $last) {
                $q->whereBetween('i.I_DEBUT', [$first->toDateString(), $last->toDateString()])
                  ->orWhereBetween('i.I_FIN',  [$first->toDateString(), $last->toDateString()])
                  ->orWhere(function ($inner) use ($first, $last) {
                      $inner->where('i.I_DEBUT', '<=', $first->toDateString())
                            ->where('i.I_FIN',   '>=', $last->toDateString());
                  });
            })
            ->select(
                'i.I_CODE',
                'i.I_DEBUT',
                'i.I_FIN',
                'i.I_ACCEPT',
                'i.I_COMMENT',
                'ti.TI_LIBELLE'
            )
            ->get();

        // ── Build month calendar grid ──────────────────────────────────────────
        // Group events and absences by date string for fast lookup
        $eventsByDate   = $events->groupBy('event_date');
        $absencesByDate = [];
        foreach ($absences as $abs) {
            $start = Carbon::parse($abs->I_DEBUT)->startOfDay();
            $end   = Carbon::parse($abs->I_FIN)->endOfDay();
            $cursor = $start->copy();
            while ($cursor->lte($end)) {
                $key = $cursor->format('Y-m-d');
                $absencesByDate[$key][] = $abs;
                $cursor->addDay();
            }
        }

        // Build 6-week grid starting from Monday of the first week
        $gridStart = $first->copy()->startOfWeek();
        $gridEnd   = $last->copy()->endOfWeek();
        $weeks     = [];
        $cursor    = $gridStart->copy();
        while ($cursor->lte($gridEnd)) {
            $week = [];
            for ($d = 0; $d < 7; $d++) {
                $key = $cursor->format('Y-m-d');
                $week[] = [
                    'date'     => $cursor->copy(),
                    'key'      => $key,
                    'inMonth'  => (int) $cursor->month === $month,
                    'isToday'  => $cursor->isToday(),
                    'events'   => $eventsByDate->get($key, collect()),
                    'absences' => $absencesByDate[$key] ?? [],
                ];
                $cursor->addDay();
            }
            $weeks[] = $week;
        }

        return view('planning.index', compact(
            'weeks', 'year', 'month', 'first',
            'prevYear', 'prevMonth', 'nextYear', 'nextMonth'
        ));
    }
}
