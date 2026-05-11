<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\TypeVehicule;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_type_vehicule.php
 * Legacy pattern: save
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class SaveTypeVehiculeController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_type_vehicule.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = TypeVehicule::findOrFail($id);

        return view('legacy_migrated.save_type_vehicule.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'OLD_TV_CODE' => 'nullable|string|max:255',
            'TV_CODE' => 'nullable|string|max:255',
            'TV_NB' => 'nullable|string|max:255',
            'TV_USAGE' => 'nullable|string|max:255',
            'TV_LIBELLE' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'from' => 'nullable|string|max:255',
            'ROLE_$i' => 'nullable|string|max:255',
            'PS_$i' => 'nullable|string|max:255',
        ]);

        $item = TypeVehicule::create([
            'OLD_TV_CODE' => $validated['OLD_TV_CODE'] ?? null,
            'TV_CODE' => $validated['TV_CODE'] ?? null,
            'TV_NB' => $validated['TV_NB'] ?? null,
            'TV_USAGE' => $validated['TV_USAGE'] ?? null,
            'TV_LIBELLE' => $validated['TV_LIBELLE'] ?? null,
            'icon' => $validated['icon'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'from' => $validated['from'] ?? null,
            'ROLE_$i' => $validated['ROLE_$i'] ?? null,
            'PS_$i' => $validated['PS_$i'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_type_vehicule.edit', $item->id)
            ->with('success', 'TypeVehicule created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = TypeVehicule::findOrFail($id);

        $validated = $request->validate([
            'OLD_TV_CODE' => 'nullable|string|max:255',
            'TV_CODE' => 'nullable|string|max:255',
            'TV_NB' => 'nullable|string|max:255',
            'TV_USAGE' => 'nullable|string|max:255',
            'TV_LIBELLE' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'from' => 'nullable|string|max:255',
            'ROLE_$i' => 'nullable|string|max:255',
            'PS_$i' => 'nullable|string|max:255',
        ]);

        $item->update([
            'OLD_TV_CODE' => $validated['OLD_TV_CODE'] ?? null,
            'TV_CODE' => $validated['TV_CODE'] ?? null,
            'TV_NB' => $validated['TV_NB'] ?? null,
            'TV_USAGE' => $validated['TV_USAGE'] ?? null,
            'TV_LIBELLE' => $validated['TV_LIBELLE'] ?? null,
            'icon' => $validated['icon'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'from' => $validated['from'] ?? null,
            'ROLE_$i' => $validated['ROLE_$i'] ?? null,
            'PS_$i' => $validated['PS_$i'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_type_vehicule.edit', $item->id)
            ->with('success', 'TypeVehicule updated successfully');
    }
                

    public function destroy($id)
    {
        $item = TypeVehicule::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_type_vehicule.index')
            ->with('success', 'TypeVehicule deleted successfully');
    }
                

    public function applyOperation(Request $request)
    {
        $operation = (string) $request->input('operation');

        if ($operation === 'delete') {
            return response()->json(['status' => 'ok', 'operation' => 'delete']);
        }

        if ($operation === 'insert') {
            return response()->json(['status' => 'ok', 'operation' => 'insert']);
        }

        if ($operation === 'update') {
            return response()->json(['status' => 'ok', 'operation' => 'update']);
        }

        return response()->json(['status' => 'ignored', 'operation' => $operation]);
    }
}
