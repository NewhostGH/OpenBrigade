<?php

namespace App\Http\Controllers;

use App\Models\ObFeature;
use App\Services\FeatureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeatureController extends Controller
{
    public function index(): View
    {
        $features = app(FeatureService::class)->all()->groupBy('group');

        $groups = [
            'logistique' => ['label' => 'Logistique',     'icon' => 'boxes'],
            'personnel' => ['label' => 'Personnel & RH', 'icon' => 'users'],
            'planning' => ['label' => 'Planning',       'icon' => 'calendar-alt'],
            'operations' => ['label' => 'Opérations',     'icon' => 'ambulance'],
            'finances' => ['label' => 'Finances',       'icon' => 'euro-sign'],
            'geographie' => ['label' => 'Géographie',     'icon' => 'map'],
            'systeme' => ['label' => 'Système',        'icon' => 'cog'],
        ];

        return view('admin.fonctionnalites', compact('features', 'groups'));
    }

    public function toggle(Request $request, ObFeature $feature): RedirectResponse
    {
        $enabled = $request->boolean('enabled');

        app(FeatureService::class)->setEnabled($feature, $enabled);

        return redirect()->back()
            ->with('success', 'Fonctionnalité mise à jour.');
    }
}
