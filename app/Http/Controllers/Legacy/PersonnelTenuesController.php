<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\PersonnelTenues;
use Illuminate\Http\Request;

/**
 * Legacy migration source: personnel_tenues.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class PersonnelTenuesController extends Controller
{
    public function index(Request $request)
    {
        $query = PersonnelTenues::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('s_code', 'like', '%' . $term . '%');
                $query->orWhere('tm_description', 'like', '%' . $term . '%');
                $query->orWhere('tm_usage', 'like', '%' . $term . '%');
                $query->orWhere('tm_code', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.personnel_tenues.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.personnel_tenues.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = PersonnelTenues::findOrFail($id);

        return view('legacy_migrated.personnel_tenues.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pompier' => 'nullable|string|max:255',
            'TYPE_' => 'nullable|string|max:255',
            'MODELE_' => 'nullable|string|max:255',
            'ANNEE_' => 'nullable|string|max:255',
            'NB_' => 'nullable|string|max:255',
            'NEW_' => 'nullable|string|max:255',
            'annuler' => 'nullable|string|max:255',
            'TAILLE_' => 'nullable|string|max:255',
        ]);

        $item = PersonnelTenues::create([
            'pompier' => $validated['pompier'] ?? null,
            'TYPE_' => $validated['TYPE_'] ?? null,
            'MODELE_' => $validated['MODELE_'] ?? null,
            'ANNEE_' => $validated['ANNEE_'] ?? null,
            'NB_' => $validated['NB_'] ?? null,
            'NEW_' => $validated['NEW_'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
            'TAILLE_' => $validated['TAILLE_'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.personnel_tenues.edit', $item->id)
            ->with('success', 'PersonnelTenues created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = PersonnelTenues::findOrFail($id);

        $validated = $request->validate([
            'pompier' => 'nullable|string|max:255',
            'TYPE_' => 'nullable|string|max:255',
            'MODELE_' => 'nullable|string|max:255',
            'ANNEE_' => 'nullable|string|max:255',
            'NB_' => 'nullable|string|max:255',
            'NEW_' => 'nullable|string|max:255',
            'annuler' => 'nullable|string|max:255',
            'TAILLE_' => 'nullable|string|max:255',
        ]);

        $item->update([
            'pompier' => $validated['pompier'] ?? null,
            'TYPE_' => $validated['TYPE_'] ?? null,
            'MODELE_' => $validated['MODELE_'] ?? null,
            'ANNEE_' => $validated['ANNEE_'] ?? null,
            'NB_' => $validated['NB_'] ?? null,
            'NEW_' => $validated['NEW_'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
            'TAILLE_' => $validated['TAILLE_'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.personnel_tenues.edit', $item->id)
            ->with('success', 'PersonnelTenues updated successfully');
    }
                

    public function destroy($id)
    {
        $item = PersonnelTenues::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.personnel_tenues.index')
            ->with('success', 'PersonnelTenues deleted successfully');
    }
                
}
