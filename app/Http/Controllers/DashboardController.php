<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardService $dash): View
    {
        $user = auth()->user();
        $limit = (int) $request->query('number_events', 20);

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
        ]);
    }
}
