<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\TypeVehicule;
use Illuminate\Http\Request;

/**
 * Legacy migration source: upd_type_vehicule.php
 * Legacy pattern: edit
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class UpdTypeVehiculeController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.upd_type_vehicule.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = TypeVehicule::findOrFail($id);

        return view('legacy_migrated.upd_type_vehicule.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'operation' => 'nullable|string|max:255',
            'OLD_TV_CODE' => 'nullable|string|max:255',
            'TV_CODE' => 'nullable|string|max:255',
            'TV_LIBELLE' => 'nullable|string|max:255',
            'tab' => 'nullable|string|max:255',
            'child' => 'nullable|string|max:255',
            'upd' => 'nullable|string|max:255',
            'icone' => 'nullable|file',
            'suppr' => 'nullable|string|max:255',
            'iconsuppr' => 'nullable|string|max:255',
            'ROLE_$i' => 'nullable|string|max:255',
            'annuler' => 'nullable|string|max:255',
            'TV_USAGE' => 'nullable|string|max:255',
            'TV_NB' => 'nullable|string|max:255',
            'PS_$i' => 'nullable|string|max:255',
        ]);

        $item = TypeVehicule::create([
            'operation' => $validated['operation'] ?? null,
            'OLD_TV_CODE' => $validated['OLD_TV_CODE'] ?? null,
            'TV_CODE' => $validated['TV_CODE'] ?? null,
            'TV_LIBELLE' => $validated['TV_LIBELLE'] ?? null,
            'tab' => $validated['tab'] ?? null,
            'child' => $validated['child'] ?? null,
            'upd' => $validated['upd'] ?? null,
            'icone' => $validated['icone'] ?? null,
            'suppr' => $validated['suppr'] ?? null,
            'iconsuppr' => $validated['iconsuppr'] ?? null,
            'ROLE_$i' => $validated['ROLE_$i'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
            'TV_USAGE' => $validated['TV_USAGE'] ?? null,
            'TV_NB' => $validated['TV_NB'] ?? null,
            'PS_$i' => $validated['PS_$i'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_type_vehicule.edit', $item->id)
            ->with('success', 'TypeVehicule created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = TypeVehicule::findOrFail($id);

        $validated = $request->validate([
            'operation' => 'nullable|string|max:255',
            'OLD_TV_CODE' => 'nullable|string|max:255',
            'TV_CODE' => 'nullable|string|max:255',
            'TV_LIBELLE' => 'nullable|string|max:255',
            'tab' => 'nullable|string|max:255',
            'child' => 'nullable|string|max:255',
            'upd' => 'nullable|string|max:255',
            'icone' => 'nullable|file',
            'suppr' => 'nullable|string|max:255',
            'iconsuppr' => 'nullable|string|max:255',
            'ROLE_$i' => 'nullable|string|max:255',
            'annuler' => 'nullable|string|max:255',
            'TV_USAGE' => 'nullable|string|max:255',
            'TV_NB' => 'nullable|string|max:255',
            'PS_$i' => 'nullable|string|max:255',
        ]);

        $item->update([
            'operation' => $validated['operation'] ?? null,
            'OLD_TV_CODE' => $validated['OLD_TV_CODE'] ?? null,
            'TV_CODE' => $validated['TV_CODE'] ?? null,
            'TV_LIBELLE' => $validated['TV_LIBELLE'] ?? null,
            'tab' => $validated['tab'] ?? null,
            'child' => $validated['child'] ?? null,
            'upd' => $validated['upd'] ?? null,
            'icone' => $validated['icone'] ?? null,
            'suppr' => $validated['suppr'] ?? null,
            'iconsuppr' => $validated['iconsuppr'] ?? null,
            'ROLE_$i' => $validated['ROLE_$i'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
            'TV_USAGE' => $validated['TV_USAGE'] ?? null,
            'TV_NB' => $validated['TV_NB'] ?? null,
            'PS_$i' => $validated['PS_$i'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_type_vehicule.edit', $item->id)
            ->with('success', 'TypeVehicule updated successfully');
    }
                

    public function destroy($id)
    {
        $item = TypeVehicule::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.upd_type_vehicule.index')
            ->with('success', 'TypeVehicule deleted successfully');
    }
                

    public function applyOperation(Request $request)
    {
        $operation = (string) $request->input('operation');

        if ($operation === 'insert') {
            return response()->json(['status' => 'ok', 'operation' => 'insert']);
        }

        if ($operation === 'update') {
            return response()->json(['status' => 'ok', 'operation' => 'update']);
        }

        return response()->json(['status' => 'ignored', 'operation' => $operation]);
    }
}
