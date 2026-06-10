<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Services\PersonnelExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StatistiqueController extends Controller
{
    // ── KPI dashboard ─────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        return view('statistique.index', $this->fetchStats($request));
    }

    // ── Bilan annuel — Généralités ────────────────────────────────────────────

    public function bilanGeneralites(Request $request): View
    {
        return view('statistique.bilan.generalites', $this->fetchGeneralites($request));
    }

    // ── Bilan annuel — Activités opérationnelles ──────────────────────────────

    public function bilanActivites(Request $request): View
    {
        return view('statistique.bilan.activites', $this->fetchActivites($request));
    }

    // ── Bilan annuel — Formations ─────────────────────────────────────────────

    public function bilanFormations(Request $request): View
    {
        return view('statistique.bilan.formations', $this->fetchFormations($request));
    }

    // ── Private data fetchers ─────────────────────────────────────────────────

    private function fetchGeneralites(Request $request): array
    {
        $base = $this->bilanBase($request);
        $sectionId = $base['sectionId'];

        $totalMembers = DB::table('pompier')
            ->where('P_SECTION', $sectionId)
            ->where('P_OLD_MEMBER', 0)
            ->where('GP_ID', '<>', -1)
            ->whereNull('P_FIN')
            ->count();

        $membersByGroup = DB::table('pompier as p')
            ->leftJoin('groupe as g', 'g.GP_ID', '=', 'p.GP_ID')
            ->where('p.P_SECTION', $sectionId)
            ->where('p.P_OLD_MEMBER', 0)
            ->where('p.GP_ID', '<>', -1)
            ->whereNull('p.P_FIN')
            ->groupBy('p.GP_ID', 'g.GP_DESCRIPTION')
            ->select(DB::raw("COALESCE(g.GP_DESCRIPTION, CONCAT('Groupe ', p.GP_ID)) as label"), DB::raw('COUNT(*) as nb'))
            ->orderByDesc('nb')
            ->pluck('nb', 'label')
            ->toArray();

        $newMembersByYear = DB::table('pompier')
            ->where('P_SECTION', $sectionId)
            ->where('P_OLD_MEMBER', 0)
            ->whereNotNull('P_DATE_ENGAGEMENT')
            ->whereRaw('YEAR(P_DATE_ENGAGEMENT) BETWEEN ? AND ?', [now()->year - 4, now()->year])
            ->groupBy(DB::raw('YEAR(P_DATE_ENGAGEMENT)'))
            ->select(DB::raw('YEAR(P_DATE_ENGAGEMENT) as yr'), DB::raw('COUNT(*) as nb'))
            ->pluck('nb', 'yr')
            ->toArray();

        $vehiclesByType = DB::table('vehicule as v')
            ->leftJoin('type_vehicule as tv', 'tv.TV_CODE', '=', 'v.TV_CODE')
            ->where('v.S_ID', $sectionId)
            ->groupBy('v.TV_CODE', 'tv.TV_LIBELLE')
            ->select(DB::raw("COALESCE(tv.TV_LIBELLE, v.TV_CODE, 'Non classé') as label"), DB::raw('COUNT(*) as nb'))
            ->orderByDesc('nb')
            ->get();

        $materielsByType = DB::table('materiel as m')
            ->leftJoin('type_materiel as tm', 'tm.TM_ID', '=', 'm.TM_ID')
            ->where('m.S_ID', $sectionId)
            ->groupBy('m.TM_ID', 'tm.TM_DESCRIPTION')
            ->select(DB::raw("COALESCE(tm.TM_DESCRIPTION, 'Non classé') as label"), DB::raw('COUNT(*) as nb'))
            ->orderByDesc('nb')
            ->get();

        $consommablesByType = DB::table('consommable as c')
            ->leftJoin('type_consommable as tc', 'tc.TC_ID', '=', 'c.TC_ID')
            ->where('c.S_ID', $sectionId)
            ->groupBy('c.TC_ID', 'tc.TC_DESCRIPTION')
            ->select(DB::raw("COALESCE(tc.TC_DESCRIPTION, 'Non classé') as label"), DB::raw('COUNT(*) as nb'))
            ->orderByDesc('nb')
            ->get();

        return array_merge($base, [
            'totalMembers' => $totalMembers,
            'membersByGroup' => $membersByGroup,
            'newMembersByYear' => $newMembersByYear,
            'vehiclesByType' => $vehiclesByType,
            'totalVehicles' => $vehiclesByType->sum('nb'),
            'materielsByType' => $materielsByType,
            'totalMateriels' => $materielsByType->sum('nb'),
            'consommablesByType' => $consommablesByType,
            'totalConsommables' => $consommablesByType->sum('nb'),
        ]);
    }

    private function fetchActivites(Request $request): array
    {
        $base = $this->bilanBase($request);
        $sectionId = $base['sectionId'];
        $year = $base['year'];

        $eventsByMonthRaw = DB::table('evenement as e')
            ->join('evenement_horaire as eh', 'e.E_CODE', '=', 'eh.E_CODE')
            ->where('e.S_ID', $sectionId)
            ->where('e.E_CANCELED', 0)
            ->whereYear('eh.EH_DATE_DEBUT', $year)
            ->where('e.TE_CODE', '<>', 'MC')
            ->groupBy(DB::raw('MONTH(eh.EH_DATE_DEBUT)'))
            ->select(DB::raw('MONTH(eh.EH_DATE_DEBUT) as month'), DB::raw('COUNT(DISTINCT e.E_CODE) as nb'))
            ->pluck('nb', 'month')
            ->toArray();

        $participationByMonth = DB::select(
            'SELECT MONTH(eh.EH_DATE_DEBUT) as month,
                    COUNT(DISTINCT ep.P_ID) as nb_persons,
                    SUM(TIMESTAMPDIFF(HOUR, eh.EH_DEBUT, eh.EH_FIN)) as hours
             FROM evenement_participation ep
             JOIN evenement_horaire eh ON ep.E_CODE=eh.E_CODE AND ep.EH_ID=eh.EH_ID
             JOIN evenement e ON e.E_CODE=ep.E_CODE
             WHERE e.S_ID=? AND YEAR(eh.EH_DATE_DEBUT)=? AND ep.EP_ABSENT=0 AND e.E_CANCELED=0 AND e.TE_CODE <> "MC"
             GROUP BY MONTH(eh.EH_DATE_DEBUT)',
            [$sectionId, $year]
        );
        $partByMonth = collect($participationByMonth)->keyBy('month');

        $eventsByType = DB::table('evenement as e')
            ->join('evenement_horaire as eh', 'e.E_CODE', '=', 'eh.E_CODE')
            ->leftJoin('type_evenement as te', 'te.TE_CODE', '=', 'e.TE_CODE')
            ->where('e.S_ID', $sectionId)
            ->where('e.E_CANCELED', 0)
            ->whereYear('eh.EH_DATE_DEBUT', $year)
            ->where('e.TE_CODE', '<>', 'MC')
            ->groupBy('e.TE_CODE', 'te.TE_LIBELLE')
            ->select(DB::raw('COALESCE(te.TE_LIBELLE, e.TE_CODE) as label'), DB::raw('COUNT(DISTINCT e.E_CODE) as nb'))
            ->orderByDesc('nb')
            ->pluck('nb', 'label')
            ->toArray();

        $topParticipants = DB::table('evenement_participation as ep')
            ->join('pompier as p', 'ep.P_ID', '=', 'p.P_ID')
            ->join('evenement_horaire as eh', function ($j) {
                $j->on('eh.E_CODE', '=', 'ep.E_CODE')->on('eh.EH_ID', '=', 'ep.EH_ID');
            })
            ->join('evenement as e', 'ep.E_CODE', '=', 'e.E_CODE')
            ->where('e.S_ID', $sectionId)
            ->where('ep.EP_ABSENT', 0)
            ->where('e.E_CANCELED', 0)
            ->where('e.TE_CODE', '<>', 'MC')
            ->whereYear('eh.EH_DATE_DEBUT', $year)
            ->groupBy('ep.P_ID', 'p.P_NOM', 'p.P_PRENOM')
            ->select('ep.P_ID', 'p.P_NOM', 'p.P_PRENOM', DB::raw('COUNT(DISTINCT ep.E_CODE) as nb_events'))
            ->orderByDesc('nb_events')
            ->limit(10)
            ->get();

        $months = range(1, 12);
        $eventsData = array_map(fn ($m) => $eventsByMonthRaw[$m] ?? 0, $months);
        $participantData = array_map(fn ($m) => $partByMonth->has($m) ? (int) $partByMonth->get($m)->nb_persons : 0, $months);

        return array_merge($base, [
            'eventsData' => $eventsData,
            'participantData' => $participantData,
            'eventsByType' => $eventsByType,
            'topParticipants' => $topParticipants,
            'totalEvents' => array_sum($eventsData),
            'totalParticipants' => array_sum($participantData),
            'totalHours' => (int) collect($participationByMonth)->sum('hours'),
        ]);
    }

    private function fetchFormations(Request $request): array
    {
        $base = $this->bilanBase($request);
        $sectionId = $base['sectionId'];
        $year = $base['year'];

        $formWhere = "LOWER(COALESCE(c.CEV_DESCRIPTION, '')) LIKE '%form%' OR LOWER(te.TE_LIBELLE) LIKE '%form%'";

        $eventsByMonthRaw = DB::table('evenement as e')
            ->join('evenement_horaire as eh', 'e.E_CODE', '=', 'eh.E_CODE')
            ->join('type_evenement as te', 'te.TE_CODE', '=', 'e.TE_CODE')
            ->leftJoin('categorie_evenement as c', 'c.CEV_CODE', '=', 'te.CEV_CODE')
            ->where('e.S_ID', $sectionId)
            ->where('e.E_CANCELED', 0)
            ->whereYear('eh.EH_DATE_DEBUT', $year)
            ->whereRaw("($formWhere)")
            ->groupBy(DB::raw('MONTH(eh.EH_DATE_DEBUT)'))
            ->select(DB::raw('MONTH(eh.EH_DATE_DEBUT) as month'), DB::raw('COUNT(DISTINCT e.E_CODE) as nb'))
            ->pluck('nb', 'month')
            ->toArray();

        $eventsByType = DB::table('evenement as e')
            ->join('evenement_horaire as eh', 'e.E_CODE', '=', 'eh.E_CODE')
            ->join('type_evenement as te', 'te.TE_CODE', '=', 'e.TE_CODE')
            ->leftJoin('categorie_evenement as c', 'c.CEV_CODE', '=', 'te.CEV_CODE')
            ->where('e.S_ID', $sectionId)
            ->where('e.E_CANCELED', 0)
            ->whereYear('eh.EH_DATE_DEBUT', $year)
            ->whereRaw("($formWhere)")
            ->groupBy('te.TE_CODE', 'te.TE_LIBELLE')
            ->select(DB::raw('COALESCE(te.TE_LIBELLE, te.TE_CODE) as label'), DB::raw('COUNT(DISTINCT e.E_CODE) as nb'))
            ->orderByDesc('nb')
            ->pluck('nb', 'label')
            ->toArray();

        $formationsList = DB::table('evenement as e')
            ->join('evenement_horaire as eh', 'e.E_CODE', '=', 'eh.E_CODE')
            ->join('type_evenement as te', 'te.TE_CODE', '=', 'e.TE_CODE')
            ->leftJoin('categorie_evenement as c', 'c.CEV_CODE', '=', 'te.CEV_CODE')
            ->leftJoin('evenement_participation as ep', function ($j) {
                $j->on('ep.E_CODE', '=', 'e.E_CODE')
                    ->on('ep.EH_ID', '=', 'eh.EH_ID')
                    ->where('ep.EP_ABSENT', 0);
            })
            ->where('e.S_ID', $sectionId)
            ->where('e.E_CANCELED', 0)
            ->whereYear('eh.EH_DATE_DEBUT', $year)
            ->whereRaw("($formWhere)")
            ->groupBy('e.E_CODE', 'e.E_LIBELLE', 'e.E_LIEU', 'eh.EH_DATE_DEBUT', 'eh.EH_DEBUT', 'eh.EH_FIN', 'te.TE_LIBELLE')
            ->select(
                'e.E_CODE',
                'e.E_LIBELLE as label',
                'e.E_LIEU as lieu',
                'eh.EH_DATE_DEBUT as date',
                'te.TE_LIBELLE as type',
                DB::raw('GREATEST(TIMESTAMPDIFF(HOUR, eh.EH_DEBUT, eh.EH_FIN), 0) as duree_h'),
                DB::raw('COUNT(DISTINCT ep.P_ID) as nb_participants')
            )
            ->orderBy('eh.EH_DATE_DEBUT')
            ->get();

        $months = range(1, 12);
        $eventsData = array_map(fn ($m) => $eventsByMonthRaw[$m] ?? 0, $months);

        return array_merge($base, [
            'eventsData' => $eventsData,
            'eventsByType' => $eventsByType,
            'formationsList' => $formationsList,
            'totalFormations' => array_sum($eventsData),
            'totalHours' => (int) $formationsList->sum('duree_h'),
            'totalTrained' => (int) $formationsList->sum('nb_participants'),
        ]);
    }

    // ── Shared helpers ────────────────────────────────────────────────────────

    private function bilanBase(Request $request): array
    {
        $sectionId = (int) auth()->user()->P_SECTION;
        $year = (int) $request->integer('year', now()->year);
        $years = range(now()->year, max(now()->year - 5, 2018));
        $section = DB::table('section')->where('S_ID', $sectionId)->first(['S_CODE', 'S_DESCRIPTION']);
        $letterhead = (new PersonnelExportService)->letterheadConfig(Section::find($sectionId));

        return compact('year', 'years', 'sectionId', 'section', 'letterhead');
    }

    private function fetchStats(Request $request): array
    {
        $sectionId = (int) auth()->user()->P_SECTION;
        $year = (int) $request->integer('year', now()->year);
        $years = range(now()->year, max(now()->year - 5, 2018));

        $eventsByMonth = DB::table('evenement as e')
            ->join('evenement_horaire as eh', 'e.E_CODE', '=', 'eh.E_CODE')
            ->where('e.S_ID', $sectionId)
            ->where('e.E_CANCELED', 0)
            ->whereYear('eh.EH_DATE_DEBUT', $year)
            ->where('e.TE_CODE', '<>', 'MC')
            ->groupBy(DB::raw('MONTH(eh.EH_DATE_DEBUT)'))
            ->select(DB::raw('MONTH(eh.EH_DATE_DEBUT) as month'), DB::raw('COUNT(DISTINCT e.E_CODE) as nb'))
            ->pluck('nb', 'month')
            ->toArray();

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

        $newMembersByYear = DB::table('pompier')
            ->where('P_SECTION', $sectionId)
            ->where('P_OLD_MEMBER', 0)
            ->whereNotNull('P_DATE_ENGAGEMENT')
            ->whereRaw('YEAR(P_DATE_ENGAGEMENT) BETWEEN ? AND ?', [now()->year - 4, now()->year])
            ->groupBy(DB::raw('YEAR(P_DATE_ENGAGEMENT)'))
            ->select(DB::raw('YEAR(P_DATE_ENGAGEMENT) as yr'), DB::raw('COUNT(*) as nb'))
            ->pluck('nb', 'yr')
            ->toArray();

        $topParticipants = DB::table('evenement_participation as ep')
            ->join('pompier as p', 'ep.P_ID', '=', 'p.P_ID')
            ->join('evenement_horaire as eh', function ($j) {
                $j->on('eh.E_CODE', '=', 'ep.E_CODE')->on('eh.EH_ID', '=', 'ep.EH_ID');
            })
            ->join('evenement as e', 'ep.E_CODE', '=', 'e.E_CODE')
            ->where('e.S_ID', $sectionId)
            ->where('ep.EP_ABSENT', 0)
            ->where('e.E_CANCELED', 0)
            ->whereYear('eh.EH_DATE_DEBUT', $year)
            ->groupBy('ep.P_ID', 'p.P_NOM', 'p.P_PRENOM')
            ->select('ep.P_ID', 'p.P_NOM', 'p.P_PRENOM', DB::raw('COUNT(DISTINCT ep.E_CODE) as nb_events'))
            ->orderByDesc('nb_events')
            ->limit(10)
            ->get();

        $eventsByType = DB::table('evenement as e')
            ->join('evenement_horaire as eh', 'e.E_CODE', '=', 'eh.E_CODE')
            ->leftJoin('type_evenement as te', 'te.TE_CODE', '=', 'e.TE_CODE')
            ->where('e.S_ID', $sectionId)
            ->where('e.E_CANCELED', 0)
            ->whereYear('eh.EH_DATE_DEBUT', $year)
            ->groupBy('e.TE_CODE', 'te.TE_LIBELLE')
            ->select(DB::raw('COALESCE(te.TE_LIBELLE, e.TE_CODE) as label'), DB::raw('COUNT(DISTINCT e.E_CODE) as nb'))
            ->pluck('nb', 'label')
            ->toArray();

        $totalMembers = DB::table('pompier')
            ->where('P_SECTION', $sectionId)
            ->where('P_OLD_MEMBER', 0)
            ->where('GP_ID', '<>', -1)
            ->whereNull('P_FIN')
            ->count();

        $months = range(1, 12);
        $eventsData = array_map(fn ($m) => $eventsByMonth[$m] ?? 0, $months);
        $participantData = array_map(fn ($m) => $partByMonth->has($m) ? (int) $partByMonth->get($m)->nb_persons : 0, $months);

        return [
            'year' => $year,
            'years' => $years,
            'sectionId' => $sectionId,
            'eventsData' => $eventsData,
            'participantData' => $participantData,
            'newMembersByYear' => $newMembersByYear,
            'topParticipants' => $topParticipants,
            'eventsByType' => $eventsByType,
            'totalMembers' => $totalMembers,
            'totalEvents' => array_sum($eventsData),
            'totalParticipants' => array_sum($participantData),
            'totalHours' => (int) collect($participationByMonth)->sum('hours'),
            'newMembersThisYear' => (int) ($newMembersByYear[$year] ?? 0),
        ];
    }
}
