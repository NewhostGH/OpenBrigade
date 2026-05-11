<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EvenementTarifFormation;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_tarif_formation.php
 * Legacy pattern: generic
 * Legacy permission id: 41
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementTarifFormationController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementTarifFormation::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('e_tarif', 'like', '%' . $term . '%');
                $query->orWhere('s_id', 'like', '%' . $term . '%');
                $query->orWhere('p_id', 'like', '%' . $term . '%');
                $query->orWhere('e_code', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.evenement_tarif_formation.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.evenement_tarif_formation.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EvenementTarifFormation::findOrFail($id);

        return view('legacy_migrated.evenement_tarif_formation.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mode_' => 'nullable|string|max:255',
        ]);

        $item = EvenementTarifFormation::create([
            'mode_' => $validated['mode_'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_tarif_formation.edit', $item->id)
            ->with('success', 'EvenementTarifFormation created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EvenementTarifFormation::findOrFail($id);

        $validated = $request->validate([
            'mode_' => 'nullable|string|max:255',
        ]);

        $item->update([
            'mode_' => $validated['mode_'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_tarif_formation.edit', $item->id)
            ->with('success', 'EvenementTarifFormation updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EvenementTarifFormation::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenement_tarif_formation.index')
            ->with('success', 'EvenementTarifFormation deleted successfully');
    }
                
}
