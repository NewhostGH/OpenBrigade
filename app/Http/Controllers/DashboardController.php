<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardService $dash): View
    {
        $user = auth()->user();
        $limit = (int) $request->query('number_events', 20);
        $layout = $dash->getWidgetLayout($user);

        return view('dashboard.index', [
            'stats' => $dash->getStats($user),
            'passwordExpiry' => $dash->getPasswordExpiry($user),
            'competenceAlerts' => $dash->getCompetenceWarnings($user),
            'welcome' => $dash->getWelcome($user),
            'myActivities' => $dash->getMyActivities($user),
            'events' => $dash->getEvents($user, $limit),
            'duty' => $dash->getDuty($user),
            'infos' => $dash->getInfos($user),
            'birthdays' => $dash->getBirthdays($user),
            'vehicles' => $dash->getVehiclesAlerts($user),
            'consumables' => $dash->getConsommablesAlerts($user),
            'cp' => $dash->getCpAlerts($user),
            'horaires' => $dash->getHorairesAlerts($user),
            'remplacements' => $dash->getRemplacementsAlerts($user),
            'replacementRequests' => $dash->getReplacementRequests($user),
            'unpaidActivities' => $dash->getUnpaidActivities($user),
            'missingStats' => $dash->getMissingStats($user),
            'expenses' => $dash->getExpenses($user),
            'training' => $dash->getTraining($user),
            'mc' => $dash->getMcEvents($user),
            'sectionLinks' => $dash->getSectionLinks($user),
            'about' => $dash->getAbout($user),
            'numberEvents' => $limit,
            'widgetsByColumn' => $layout['columns'],
            'hiddenWidgets' => $layout['hidden'],
        ]);
    }

    public function saveLayout(Request $request): JsonResponse
    {
        $pid = (int) auth()->user()->P_ID;
        $layout = $request->input('layout', []);
        $allowed = array_column(DashboardService::WIDGET_DEFAULTS, 'key');

        DB::transaction(function () use ($pid, $layout, $allowed) {
            DB::table('ob_dashboard_layout')->where('P_ID', $pid)->delete();
            foreach ($layout as $i => $item) {
                $key = $item['key'] ?? '';
                $col = (int) ($item['col'] ?? 1);
                if (! in_array($key, $allowed, true) || $col < 1 || $col > 3) {
                    continue;
                }
                DB::table('ob_dashboard_layout')->insert([
                    'P_ID' => $pid,
                    'widget_key' => $key,
                    'col' => $col,
                    'position' => (int) ($item['position'] ?? $i),
                    'visible' => isset($item['visible']) ? (int) (bool) $item['visible'] : 1,
                ]);
            }
        });

        return response()->json(['ok' => true]);
    }
}
