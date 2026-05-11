<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\TypeMateriel;
use Illuminate\Http\Request;

/**
 * Legacy migration source: upd_type_materiel.php
 * Legacy pattern: edit
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class UpdTypeMaterielController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.upd_type_materiel.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = TypeMateriel::findOrFail($id);

        return view('legacy_migrated.upd_type_materiel.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'TM_ID' => 'nullable|string|max:255',
            'TM_CODE' => 'nullable|string|max:255',
            'TM_USAGE' => 'nullable|string|max:255',
            'TM_LOT' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'TM_DESCRIPTION' => 'nullable|string|max:255',
            'annuler' => 'nullable|string|max:255',
            'TT_CODE' => 'nullable|string|max:255',
        ]);

        $item = TypeMateriel::create([
            'TM_ID' => $validated['TM_ID'] ?? null,
            'TM_CODE' => $validated['TM_CODE'] ?? null,
            'TM_USAGE' => $validated['TM_USAGE'] ?? null,
            'TM_LOT' => $validated['TM_LOT'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'TM_DESCRIPTION' => $validated['TM_DESCRIPTION'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
            'TT_CODE' => $validated['TT_CODE'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_type_materiel.edit', $item->id)
            ->with('success', 'TypeMateriel created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = TypeMateriel::findOrFail($id);

        $validated = $request->validate([
            'TM_ID' => 'nullable|string|max:255',
            'TM_CODE' => 'nullable|string|max:255',
            'TM_USAGE' => 'nullable|string|max:255',
            'TM_LOT' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'TM_DESCRIPTION' => 'nullable|string|max:255',
            'annuler' => 'nullable|string|max:255',
            'TT_CODE' => 'nullable|string|max:255',
        ]);

        $item->update([
            'TM_ID' => $validated['TM_ID'] ?? null,
            'TM_CODE' => $validated['TM_CODE'] ?? null,
            'TM_USAGE' => $validated['TM_USAGE'] ?? null,
            'TM_LOT' => $validated['TM_LOT'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'TM_DESCRIPTION' => $validated['TM_DESCRIPTION'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
            'TT_CODE' => $validated['TT_CODE'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_type_materiel.edit', $item->id)
            ->with('success', 'TypeMateriel updated successfully');
    }
                

    public function destroy($id)
    {
        $item = TypeMateriel::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.upd_type_materiel.index')
            ->with('success', 'TypeMateriel deleted successfully');
    }
                
}
