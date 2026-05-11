<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\TypeEvenement;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_type_evenement.php
 * Legacy pattern: save
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class SaveTypeEvenementController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_type_evenement.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = TypeEvenement::findOrFail($id);

        return view('legacy_migrated.save_type_evenement.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'OLD_TE_CODE' => 'nullable|string|max:255',
            'TE_CODE' => 'nullable|string|max:255',
            'CEV_CODE' => 'nullable|string|max:255',
            'TE_LIBELLE' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'TE_MULTI_DUPLI' => 'nullable|string|max:255',
            'TE_MAIN_COURANTE' => 'nullable|string|max:255',
            'TE_VICTIMES' => 'nullable|string|max:255',
            'ACCES_RESTREINT' => 'nullable|string|max:255',
            'TE_PERSONNEL' => 'nullable|string|max:255',
            'TE_VEHICULES' => 'nullable|string|max:255',
            'TE_MATERIEL' => 'nullable|string|max:255',
            'TE_CONSOMMABLES' => 'nullable|string|max:255',
            'COLONNE_RENFORT' => 'nullable|string|max:255',
            'REMPLACEMENT' => 'nullable|string|max:255',
            'PIQUET' => 'nullable|string|max:255',
            'TE_MAP' => 'nullable|string|max:255',
            'CLIENT' => 'nullable|string|max:255',
            'TE_DPS' => 'nullable|string|max:255',
            'TE_DOCUMENT' => 'nullable|string|max:255',
        ]);

        $item = TypeEvenement::create([
            'OLD_TE_CODE' => $validated['OLD_TE_CODE'] ?? null,
            'TE_CODE' => $validated['TE_CODE'] ?? null,
            'CEV_CODE' => $validated['CEV_CODE'] ?? null,
            'TE_LIBELLE' => $validated['TE_LIBELLE'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'TE_MULTI_DUPLI' => $validated['TE_MULTI_DUPLI'] ?? null,
            'TE_MAIN_COURANTE' => $validated['TE_MAIN_COURANTE'] ?? null,
            'TE_VICTIMES' => $validated['TE_VICTIMES'] ?? null,
            'ACCES_RESTREINT' => $validated['ACCES_RESTREINT'] ?? null,
            'TE_PERSONNEL' => $validated['TE_PERSONNEL'] ?? null,
            'TE_VEHICULES' => $validated['TE_VEHICULES'] ?? null,
            'TE_MATERIEL' => $validated['TE_MATERIEL'] ?? null,
            'TE_CONSOMMABLES' => $validated['TE_CONSOMMABLES'] ?? null,
            'COLONNE_RENFORT' => $validated['COLONNE_RENFORT'] ?? null,
            'REMPLACEMENT' => $validated['REMPLACEMENT'] ?? null,
            'PIQUET' => $validated['PIQUET'] ?? null,
            'TE_MAP' => $validated['TE_MAP'] ?? null,
            'CLIENT' => $validated['CLIENT'] ?? null,
            'TE_DPS' => $validated['TE_DPS'] ?? null,
            'TE_DOCUMENT' => $validated['TE_DOCUMENT'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_type_evenement.edit', $item->id)
            ->with('success', 'TypeEvenement created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = TypeEvenement::findOrFail($id);

        $validated = $request->validate([
            'OLD_TE_CODE' => 'nullable|string|max:255',
            'TE_CODE' => 'nullable|string|max:255',
            'CEV_CODE' => 'nullable|string|max:255',
            'TE_LIBELLE' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'TE_MULTI_DUPLI' => 'nullable|string|max:255',
            'TE_MAIN_COURANTE' => 'nullable|string|max:255',
            'TE_VICTIMES' => 'nullable|string|max:255',
            'ACCES_RESTREINT' => 'nullable|string|max:255',
            'TE_PERSONNEL' => 'nullable|string|max:255',
            'TE_VEHICULES' => 'nullable|string|max:255',
            'TE_MATERIEL' => 'nullable|string|max:255',
            'TE_CONSOMMABLES' => 'nullable|string|max:255',
            'COLONNE_RENFORT' => 'nullable|string|max:255',
            'REMPLACEMENT' => 'nullable|string|max:255',
            'PIQUET' => 'nullable|string|max:255',
            'TE_MAP' => 'nullable|string|max:255',
            'CLIENT' => 'nullable|string|max:255',
            'TE_DPS' => 'nullable|string|max:255',
            'TE_DOCUMENT' => 'nullable|string|max:255',
        ]);

        $item->update([
            'OLD_TE_CODE' => $validated['OLD_TE_CODE'] ?? null,
            'TE_CODE' => $validated['TE_CODE'] ?? null,
            'CEV_CODE' => $validated['CEV_CODE'] ?? null,
            'TE_LIBELLE' => $validated['TE_LIBELLE'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'TE_MULTI_DUPLI' => $validated['TE_MULTI_DUPLI'] ?? null,
            'TE_MAIN_COURANTE' => $validated['TE_MAIN_COURANTE'] ?? null,
            'TE_VICTIMES' => $validated['TE_VICTIMES'] ?? null,
            'ACCES_RESTREINT' => $validated['ACCES_RESTREINT'] ?? null,
            'TE_PERSONNEL' => $validated['TE_PERSONNEL'] ?? null,
            'TE_VEHICULES' => $validated['TE_VEHICULES'] ?? null,
            'TE_MATERIEL' => $validated['TE_MATERIEL'] ?? null,
            'TE_CONSOMMABLES' => $validated['TE_CONSOMMABLES'] ?? null,
            'COLONNE_RENFORT' => $validated['COLONNE_RENFORT'] ?? null,
            'REMPLACEMENT' => $validated['REMPLACEMENT'] ?? null,
            'PIQUET' => $validated['PIQUET'] ?? null,
            'TE_MAP' => $validated['TE_MAP'] ?? null,
            'CLIENT' => $validated['CLIENT'] ?? null,
            'TE_DPS' => $validated['TE_DPS'] ?? null,
            'TE_DOCUMENT' => $validated['TE_DOCUMENT'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_type_evenement.edit', $item->id)
            ->with('success', 'TypeEvenement updated successfully');
    }
                

    public function destroy($id)
    {
        $item = TypeEvenement::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_type_evenement.index')
            ->with('success', 'TypeEvenement deleted successfully');
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
