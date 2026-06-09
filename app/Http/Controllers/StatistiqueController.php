<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StatistiqueController extends Controller
{
    /**
     * Statistics dashboard — participation rates, activity counts, new members.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $sectionId = (int) $user->P_SECTION;

        $year = (int) $request->integer('year', now()->year);
        $years = range(now()->year, max(now()->year - 5, 2018));

        // Events per month for the year
        $eventsByMonth = DB::table('evenement as e')
            ->join('evenement_horaire as eh', 'e.E_CODE', '=', 'eh.E_CODE')
            ->where('e.S_ID', $sectionId)
            ->where('e.E_CANCELED', 0)
            ->whereYear('eh.EH_DATE_DEBUT', $year)
            ->where('e.TE_CODE', '<>', 'MC')
            ->groupBy(DB::raw('MONTH(eh.EH_DATE_DEBUT)'))
            ->select(
                DB::raw('MONTH(eh.EH_DATE_DEBUT) as month'),
                DB::raw('COUNT(DISTINCT e.E_CODE) as nb')
            )
            ->pluck('nb', 'month')
            ->toArray();

        // Participation (hours) per month
        $participationByMonth = DB::select(
            'SELECT MONTH(eh.EH_DATE_DEBUT) as month,
                    COUNT(DISTINCT ep.P_ID) as nb_persons,
                    SUM(TIMESTAMPDIFF(HOUR, eh.EH_DEBUT, eh.EH_FIN)) as hours
             FROM evenement_participation ep
             JOIN evenement_horaire eh ON ep.E_CODE=eh.E_CODE AND ep.EH_ID=eh.EH_ID
             JOIN evenement e ON e.E_CODE=ep.E_CODE
             WHERE e.S_ID=? AND YEAR(eh.EH_DATE_DEBUT)=? AND ep.EP_ABSENT=0 AND e.E_CANCELED=0
             GROUP BY MONTH(eh.EH_DATE_DEBUT)',
            [$sectionId, $year]
        );

        $partByMonth = collect($participationByMonth)->keyBy('month');

        // New members per year (last 5 years)
        $newMembersByYear = DB::table('pompier')
            ->where('P_SECTION', $sectionId)
            ->where('P_OLD_MEMBER', 0)
            ->whereNotNull('P_DATE_ENGAGEMENT')
            ->whereRaw('YEAR(P_DATE_ENGAGEMENT) BETWEEN ? AND ?', [now()->year - 4, now()->year])
            ->groupBy(DB::raw('YEAR(P_DATE_ENGAGEMENT)'))
            ->select(DB::raw('YEAR(P_DATE_ENGAGEMENT) as yr'), DB::raw('COUNT(*) as nb'))
            ->pluck('nb', 'yr')
            ->toArray();

        // Top participants this year
        $topParticipants = DB::table('evenement_participation as ep')
            ->join('pompier as p', 'ep.P_ID', '=', 'p.P_ID')
            ->join('evenement_horaire as eh', function ($j) {
                $j->on('eh.E_CODE', '=', 'ep.E_CODE')
                    ->on('eh.EH_ID', '=', 'ep.EH_ID');
            })
            ->join('evenement as e', 'ep.E_CODE', '=', 'e.E_CODE')
            ->where('e.S_ID', $sectionId)
            ->where('ep.EP_ABSENT', 0)
            ->where('e.E_CANCELED', 0)
            ->whereYear('eh.EH_DATE_DEBUT', $year)
            ->groupBy('ep.P_ID', 'p.P_NOM', 'p.P_PRENOM')
            ->select(
                'ep.P_ID', 'p.P_NOM', 'p.P_PRENOM',
                DB::raw('COUNT(DISTINCT ep.E_CODE) as nb_events')
            )
            ->orderByDesc('nb_events')
            ->limit(10)
            ->get();

        // Build 12-month arrays for chart data
        $months = range(1, 12);
        $eventsData = array_map(fn ($m) => $eventsByMonth[$m] ?? 0, $months);
        $participantData = $months;
        foreach ($months as $i => $m) {
            $participantData[$i] = $partByMonth->has($m) ? (int) $partByMonth->get($m)->nb_persons : 0;
        }

        return view('statistique.index', compact(
            'year', 'years', 'eventsData', 'participantData',
            'newMembersByYear', 'topParticipants'
        ));
    }
}
