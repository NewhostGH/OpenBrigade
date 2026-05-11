<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EvenementVehiculeAdd;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_vehicule_add.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementVehiculeAddController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementVehiculeAdd::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('e_open_to_ext', 'like', '%' . $term . '%');
                $query->orWhere('s_id', 'like', '%' . $term . '%');
                $query->orWhere('ev_km', 'like', '%' . $term . '%');
                $query->orWhere('e_code', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.evenement_vehicule_add.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.evenement_vehicule_add.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EvenementVehiculeAdd::findOrFail($id);

        return view('legacy_migrated.evenement_vehicule_add.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'V_ID' => 'nullable|string|max:255',
            'km' => 'nullable|string|max:255',
            'EC' => 'nullable|string|max:255',
        ]);

        $item = EvenementVehiculeAdd::create([
            'from' => $validated['from'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'action' => $validated['action'] ?? null,
            'V_ID' => $validated['V_ID'] ?? null,
            'km' => $validated['km'] ?? null,
            'EC' => $validated['EC'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_vehicule_add.edit', $item->id)
            ->with('success', 'EvenementVehiculeAdd created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EvenementVehiculeAdd::findOrFail($id);

        $validated = $request->validate([
            'from' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'V_ID' => 'nullable|string|max:255',
            'km' => 'nullable|string|max:255',
            'EC' => 'nullable|string|max:255',
        ]);

        $item->update([
            'from' => $validated['from'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'action' => $validated['action'] ?? null,
            'V_ID' => $validated['V_ID'] ?? null,
            'km' => $validated['km'] ?? null,
            'EC' => $validated['EC'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_vehicule_add.edit', $item->id)
            ->with('success', 'EvenementVehiculeAdd updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EvenementVehiculeAdd::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenement_vehicule_add.index')
            ->with('success', 'EvenementVehiculeAdd deleted successfully');
    }
                
}
