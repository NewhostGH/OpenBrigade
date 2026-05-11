<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EvenementRapport;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_rapport.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementRapportController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementRapport::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('s_id', 'like', '%' . $term . '%');
                $query->orWhere('e_libelle', 'like', '%' . $term . '%');
                $query->orWhere('te_code', 'like', '%' . $term . '%');
                $query->orWhere('te_victimes', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.evenement_rapport.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.evenement_rapport.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EvenementRapport::findOrFail($id);

        return view('legacy_migrated.evenement_rapport.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'save' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'responsable' => 'nullable|string|max:255',
            'nombres' => 'nullable|string|max:255',
            'statistiques' => 'nullable|string|max:255',
            'show_cav' => 'nullable|string|max:255',
            'show_vehicules' => 'nullable|string|max:255',
            'show_materiel' => 'nullable|string|max:255',
            'yesall' => 'nullable|string|max:255',
            'noall' => 'nullable|string|max:255',
            'check_' => 'nullable|string|max:255',
        ]);

        $item = EvenementRapport::create([
            'save' => $validated['save'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'responsable' => $validated['responsable'] ?? null,
            'nombres' => $validated['nombres'] ?? null,
            'statistiques' => $validated['statistiques'] ?? null,
            'show_cav' => $validated['show_cav'] ?? null,
            'show_vehicules' => $validated['show_vehicules'] ?? null,
            'show_materiel' => $validated['show_materiel'] ?? null,
            'yesall' => $validated['yesall'] ?? null,
            'noall' => $validated['noall'] ?? null,
            'check_' => $validated['check_'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_rapport.edit', $item->id)
            ->with('success', 'EvenementRapport created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EvenementRapport::findOrFail($id);

        $validated = $request->validate([
            'save' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'responsable' => 'nullable|string|max:255',
            'nombres' => 'nullable|string|max:255',
            'statistiques' => 'nullable|string|max:255',
            'show_cav' => 'nullable|string|max:255',
            'show_vehicules' => 'nullable|string|max:255',
            'show_materiel' => 'nullable|string|max:255',
            'yesall' => 'nullable|string|max:255',
            'noall' => 'nullable|string|max:255',
            'check_' => 'nullable|string|max:255',
        ]);

        $item->update([
            'save' => $validated['save'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'responsable' => $validated['responsable'] ?? null,
            'nombres' => $validated['nombres'] ?? null,
            'statistiques' => $validated['statistiques'] ?? null,
            'show_cav' => $validated['show_cav'] ?? null,
            'show_vehicules' => $validated['show_vehicules'] ?? null,
            'show_materiel' => $validated['show_materiel'] ?? null,
            'yesall' => $validated['yesall'] ?? null,
            'noall' => $validated['noall'] ?? null,
            'check_' => $validated['check_'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_rapport.edit', $item->id)
            ->with('success', 'EvenementRapport updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EvenementRapport::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenement_rapport.index')
            ->with('success', 'EvenementRapport deleted successfully');
    }
                
}
