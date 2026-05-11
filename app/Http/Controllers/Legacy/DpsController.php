<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Dps;
use Illuminate\Http\Request;

/**
 * Legacy migration source: dps.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class DpsController extends Controller
{
    public function index(Request $request)
    {
        $query = Dps::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('demande_pour_les_acteurs_', 'like', '%' . $term . '%');
                $query->orWhere('indicateur_p1', 'like', '%' . $term . '%');
                $query->orWhere('activit_du_rassemblement', 'like', '%' . $term . '%');
                $query->orWhere('indicateur_p2', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.dps.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.dps.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Dps::findOrFail($id);

        return view('legacy_migrated.dps.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dimNbISActeurs' => 'nullable|string|max:255',
            'P1' => 'nullable|string|max:255',
            'P' => 'nullable|string|max:255',
            'P2' => 'nullable|string|max:255',
            'E1' => 'nullable|string|max:255',
            'E2' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'dimNbISActeursCom' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'i' => 'nullable|string|max:255',
            'RIS' => 'nullable|string|max:255',
            'RISCalc' => 'nullable|string|max:255',
            'NbIS' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'commentaire' => 'nullable|string|max:255',
            'secteurs' => 'nullable|string|max:255',
            'postes' => 'nullable|string|max:255',
            'equipes' => 'nullable|string|max:255',
            'binomes' => 'nullable|string|max:255',
        ]);

        $item = Dps::create([
            'dimNbISActeurs' => $validated['dimNbISActeurs'] ?? null,
            'P1' => $validated['P1'] ?? null,
            'P' => $validated['P'] ?? null,
            'P2' => $validated['P2'] ?? null,
            'E1' => $validated['E1'] ?? null,
            'E2' => $validated['E2'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'dimNbISActeursCom' => $validated['dimNbISActeursCom'] ?? null,
            'action' => $validated['action'] ?? null,
            'i' => $validated['i'] ?? null,
            'RIS' => $validated['RIS'] ?? null,
            'RISCalc' => $validated['RISCalc'] ?? null,
            'NbIS' => $validated['NbIS'] ?? null,
            'type' => $validated['type'] ?? null,
            'commentaire' => $validated['commentaire'] ?? null,
            'secteurs' => $validated['secteurs'] ?? null,
            'postes' => $validated['postes'] ?? null,
            'equipes' => $validated['equipes'] ?? null,
            'binomes' => $validated['binomes'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.dps.edit', $item->id)
            ->with('success', 'Dps created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Dps::findOrFail($id);

        $validated = $request->validate([
            'dimNbISActeurs' => 'nullable|string|max:255',
            'P1' => 'nullable|string|max:255',
            'P' => 'nullable|string|max:255',
            'P2' => 'nullable|string|max:255',
            'E1' => 'nullable|string|max:255',
            'E2' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'dimNbISActeursCom' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'i' => 'nullable|string|max:255',
            'RIS' => 'nullable|string|max:255',
            'RISCalc' => 'nullable|string|max:255',
            'NbIS' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'commentaire' => 'nullable|string|max:255',
            'secteurs' => 'nullable|string|max:255',
            'postes' => 'nullable|string|max:255',
            'equipes' => 'nullable|string|max:255',
            'binomes' => 'nullable|string|max:255',
        ]);

        $item->update([
            'dimNbISActeurs' => $validated['dimNbISActeurs'] ?? null,
            'P1' => $validated['P1'] ?? null,
            'P' => $validated['P'] ?? null,
            'P2' => $validated['P2'] ?? null,
            'E1' => $validated['E1'] ?? null,
            'E2' => $validated['E2'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'dimNbISActeursCom' => $validated['dimNbISActeursCom'] ?? null,
            'action' => $validated['action'] ?? null,
            'i' => $validated['i'] ?? null,
            'RIS' => $validated['RIS'] ?? null,
            'RISCalc' => $validated['RISCalc'] ?? null,
            'NbIS' => $validated['NbIS'] ?? null,
            'type' => $validated['type'] ?? null,
            'commentaire' => $validated['commentaire'] ?? null,
            'secteurs' => $validated['secteurs'] ?? null,
            'postes' => $validated['postes'] ?? null,
            'equipes' => $validated['equipes'] ?? null,
            'binomes' => $validated['binomes'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.dps.edit', $item->id)
            ->with('success', 'Dps updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Dps::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.dps.index')
            ->with('success', 'Dps deleted successfully');
    }
                
}
