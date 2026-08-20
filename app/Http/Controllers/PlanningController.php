<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PlanningController extends Controller
{
    /**
     * Personal agenda — the calendar shell. Events (the activities the user is
     * signed up for + their absences) are fetched by FullCalendar from the
     * {@see self::events()} JSON feed as the user navigates.
     */
    public function index(): View
    {
        return view('planning.index');
    }

    /**
     * JSON events feed for the personal calendar. FullCalendar appends
     * ?start=&end= for the visible range; returns the signed-in user's
     * activities and absences within it as FullCalendar event objects.
     */
    public function events(Request $request): JsonResponse
    {
        $user = auth()->user();
        $pid = (int) $user->P_ID;

        $from = $request->query('start')
            ? Carbon::parse($request->query('start'))->toDateString()
            : now()->startOfMonth()->toDateString();
        $to = $request->query('end')
            ? Carbon::parse($request->query('end'))->toDateString()
            : now()->endOfMonth()->toDateString();

        $events = DB::table('evenement_participation as ep')
            ->join('evenement as e', 'ep.E_CODE', '=', 'e.E_CODE')
            ->join('evenement_horaire as eh', function ($j) {
                $j->on('eh.E_CODE', '=', 'ep.E_CODE')
                    ->on('eh.EH_ID', '=', 'ep.EH_ID');
            })
            ->join('type_evenement as te', 'e.TE_CODE', '=', 'te.TE_CODE')
            ->where('ep.P_ID', $pid)
            ->where('ep.EP_ABSENT', 0)
            ->where('e.E_CANCELED', 0)
            ->whereBetween('eh.EH_DATE_DEBUT', [$from, $to])
            ->select(
                'e.E_CODE',
                'e.E_LIBELLE',
                'e.E_CLOSED',
                DB::raw('DATE(eh.EH_DATE_DEBUT) as event_date'),
                DB::raw("TIME_FORMAT(eh.EH_DEBUT,'%H:%i') as event_time"),
                DB::raw("TIME_FORMAT(eh.EH_FIN,'%H:%i') as event_end")
            )
            ->get()
            ->map(function ($e) {
                $hasStart = $e->event_time && $e->event_time !== '00:00';
                $hasEnd = $e->event_end && $e->event_end !== '00:00';

                return [
                    'title' => $e->E_LIBELLE ?: $e->E_CODE,
                    'start' => $hasStart ? $e->event_date.'T'.$e->event_time : $e->event_date,
                    'end' => $hasStart && $hasEnd ? $e->event_date.'T'.$e->event_end : null,
                    'url' => route('event.show', $e->E_CODE),
                    'classNames' => $e->E_CLOSED ? ['fc-ev-activity', 'fc-ev-closed'] : ['fc-ev-activity'],
                ];
            });

        $absences = DB::table('indisponibilite as i')
            ->leftJoin('type_indisponibilite as ti', 'i.TI_CODE', '=', 'ti.TI_CODE')
            ->where('i.P_ID', $pid)
            ->where('i.I_CANCEL', 0)
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('i.I_DEBUT', [$from, $to])
                    ->orWhereBetween('i.I_FIN', [$from, $to])
                    ->orWhere(function ($inner) use ($from, $to) {
                        $inner->where('i.I_DEBUT', '<=', $from)
                            ->where('i.I_FIN', '>=', $to);
                    });
            })
            ->select('i.I_DEBUT', 'i.I_FIN', 'i.I_ACCEPT', 'ti.TI_LIBELLE')
            ->get()
            ->map(fn ($a) => [
                'title' => $a->TI_LIBELLE ?: __('planning.absence_default'),
                'start' => Carbon::parse($a->I_DEBUT)->toDateString(),
                // FullCalendar treats all-day `end` as exclusive.
                'end' => Carbon::parse($a->I_FIN ?: $a->I_DEBUT)->addDay()->toDateString(),
                'allDay' => true,
                'classNames' => $a->I_ACCEPT ? ['fc-ev-abs-ok'] : ['fc-ev-abs-pending'],
            ]);

        return response()->json($events->concat($absences)->values());
    }

    /**
     * Print-optimised export of the signed-in user's monthly planning — their
     * events and absences for the month as chronological lists, printed via the
     * browser dialog (Save as PDF). Same personal, self-scoped data as the
     * calendar.
     */
    public function print(Request $request): View
    {
        $user = auth()->user();
        $pid = (int) $user->P_ID;

        $year = (int) $request->integer('year', now()->year);
        $month = (int) $request->integer('month', now()->month);
        if ($month < 1) {
            $month = 12;
            $year--;
        }
        if ($month > 12) {
            $month = 1;
            $year++;
        }

        $first = Carbon::create($year, $month, 1)->startOfDay();
        $last = $first->copy()->endOfMonth();

        $events = DB::table('evenement_participation as ep')
            ->join('evenement as e', 'ep.E_CODE', '=', 'e.E_CODE')
            ->join('evenement_horaire as eh', function ($j) {
                $j->on('eh.E_CODE', '=', 'ep.E_CODE')
                    ->on('eh.EH_ID', '=', 'ep.EH_ID');
            })
            ->join('type_evenement as te', 'e.TE_CODE', '=', 'te.TE_CODE')
            ->where('ep.P_ID', $pid)
            ->where('ep.EP_ABSENT', 0)
            ->where('e.E_CANCELED', 0)
            ->whereBetween('eh.EH_DATE_DEBUT', [$first->toDateString(), $last->toDateString()])
            ->select(
                'e.E_CODE',
                'e.E_LIBELLE',
                'e.E_CLOSED',
                'te.TE_LIBELLE',
                DB::raw('DATE(eh.EH_DATE_DEBUT) as event_date'),
                DB::raw("TIME_FORMAT(eh.EH_DEBUT,'%H:%i') as event_time")
            )
            ->orderBy('eh.EH_DATE_DEBUT')
            ->get();

        $absences = DB::table('indisponibilite as i')
            ->leftJoin('type_indisponibilite as ti', 'i.TI_CODE', '=', 'ti.TI_CODE')
            ->where('i.P_ID', $pid)
            ->where('i.I_CANCEL', 0)
            ->where(function ($q) use ($first, $last) {
                $q->whereBetween('i.I_DEBUT', [$first->toDateString(), $last->toDateString()])
                    ->orWhereBetween('i.I_FIN', [$first->toDateString(), $last->toDateString()])
                    ->orWhere(function ($inner) use ($first, $last) {
                        $inner->where('i.I_DEBUT', '<=', $first->toDateString())
                            ->where('i.I_FIN', '>=', $last->toDateString());
                    });
            })
            ->select('i.I_DEBUT', 'i.I_FIN', 'i.I_ACCEPT', 'i.I_COMMENT', 'ti.TI_LIBELLE')
            ->orderBy('i.I_DEBUT')
            ->get();

        return view('planning.print', compact('events', 'absences', 'first', 'user', 'year', 'month'));
    }
}
