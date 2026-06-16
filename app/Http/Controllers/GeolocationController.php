<?php

// project: OpenBrigade

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

namespace App\Http\Controllers;

use App\Models\Personnel;
use App\Models\Section;
use App\Services\SectionScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GeolocationController extends Controller
{
    /**
     * Display the map with all members who have GPS coordinates.
     */
    public function index(Request $request): View
    {
        $sectionId = app(SectionScopeService::class)->sectionFilter($request);

        $query = DB::table('gps as g')
            ->join('pompier as p', 'g.P_ID', '=', 'p.P_ID')
            ->leftJoin('section as s', 'p.P_SECTION', '=', 's.S_ID')
            ->where('p.P_OLD_MEMBER', 0)
            ->where('p.GP_ID', '<>', -1)
            ->whereNotNull('g.LAT')
            ->whereNotNull('g.LNG')
            ->where('g.LAT', '<>', '')
            ->where('g.LNG', '<>', '')
            ->where('g.LAT', '<>', 0)
            ->where('g.LNG', '<>', 0)
            ->select(
                'p.P_ID', 'p.P_NOM', 'p.P_PRENOM', 'p.P_PHONE', 'p.P_PHOTO',
                'p.P_GRADE', 'p.P_STATUT',
                'p.P_SECTION',
                's.S_CODE',
                'g.LAT', 'g.LNG', 'g.ADDRESS',
                'g.DATE_LOC'
            );

        if ($sectionId !== null) {
            $query->where('p.P_SECTION', $sectionId);
        }

        $members = $query->orderBy('p.P_NOM')->get();

        $sections = Section::query()
            ->orderBy('S_CODE')
            ->get(['S_ID', 'S_CODE', 'S_DESCRIPTION']);

        // Transform to array for JSON
        $markers = $members->map(function ($m) {
            return [
                'id' => $m->P_ID,
                'name' => $m->P_NOM.' '.$m->P_PRENOM,
                'grade' => $m->P_GRADE,
                'phone' => $m->P_PHONE,
                'section' => $m->S_CODE,
                'statut' => $m->P_STATUT,
                'lat' => (float) $m->LAT,
                'lng' => (float) $m->LNG,
                'address' => $m->ADDRESS,
                'date_loc' => $m->DATE_LOC,
                'photo_url' => route('personnel.photo', $m->P_ID),
                'profile_url' => route('personnel.show', $m->P_ID),
            ];
        })->values()->toArray();

        return view('personnel.geolocation', [
            'markers' => $markers,
            'sections' => $sections,
            'sectionId' => $sectionId,
            'count' => count($markers),
        ]);
    }

    /**
     * Update (or create) the GPS position for a personnel member.
     */
    public function updateGps(Request $request, Personnel $personnel)
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'address' => ['nullable', 'string', 'max:200'],
        ]);

        DB::table('gps')->updateOrInsert(
            ['P_ID' => $personnel->P_ID],
            [
                'LAT' => $validated['lat'],
                'LNG' => $validated['lng'],
                'ADDRESS' => $validated['address'] ?? null,
                'DATE_LOC' => now(),
            ]
        );

        return back()->with('success', 'Position GPS mise à jour.');
    }
}
