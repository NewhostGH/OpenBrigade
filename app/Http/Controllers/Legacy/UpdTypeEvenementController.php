<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\TypeEvenement;
use Illuminate\Http\Request;

/**
 * Legacy migration source: upd_type_evenement.php
 * Legacy pattern: edit
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class UpdTypeEvenementController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.upd_type_evenement.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = TypeEvenement::findOrFail($id);

        return view('legacy_migrated.upd_type_evenement.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'OLD_TE_CODE' => 'nullable|string|max:255',
            'TE_LIBELLE' => 'nullable|string|max:255',
            'CEV_CODE' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'TE_CODE' => 'nullable|string|max:255',
            'tab' => 'nullable|string|max:255',
            'child' => 'nullable|string|max:255',
            'ope' => 'nullable|string|max:255',
            'icone' => 'nullable|file',
            'suppr' => 'nullable|string|max:255',
            'iconsuppr' => 'nullable|string|max:255',
            'TE_PERSONNEL' => 'nullable|string|max:255',
            'TE_VEHICULES' => 'nullable|string|max:255',
            'TE_MATERIEL' => 'nullable|string|max:255',
            'TE_CONSOMMABLES' => 'nullable|string|max:255',
            'TE_DOCUMENT' => 'nullable|string|max:255',
            'TE_MAP' => 'nullable|string|max:255',
            'CLIENT' => 'nullable|string|max:255',
            'TE_MAIN_COURANTE' => 'nullable|string|max:255',
            'TE_VICTIMES' => 'nullable|string|max:255',
        ]);

        $item = TypeEvenement::create([
            'OLD_TE_CODE' => $validated['OLD_TE_CODE'] ?? null,
            'TE_LIBELLE' => $validated['TE_LIBELLE'] ?? null,
            'CEV_CODE' => $validated['CEV_CODE'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'TE_CODE' => $validated['TE_CODE'] ?? null,
            'tab' => $validated['tab'] ?? null,
            'child' => $validated['child'] ?? null,
            'ope' => $validated['ope'] ?? null,
            'icone' => $validated['icone'] ?? null,
            'suppr' => $validated['suppr'] ?? null,
            'iconsuppr' => $validated['iconsuppr'] ?? null,
            'TE_PERSONNEL' => $validated['TE_PERSONNEL'] ?? null,
            'TE_VEHICULES' => $validated['TE_VEHICULES'] ?? null,
            'TE_MATERIEL' => $validated['TE_MATERIEL'] ?? null,
            'TE_CONSOMMABLES' => $validated['TE_CONSOMMABLES'] ?? null,
            'TE_DOCUMENT' => $validated['TE_DOCUMENT'] ?? null,
            'TE_MAP' => $validated['TE_MAP'] ?? null,
            'CLIENT' => $validated['CLIENT'] ?? null,
            'TE_MAIN_COURANTE' => $validated['TE_MAIN_COURANTE'] ?? null,
            'TE_VICTIMES' => $validated['TE_VICTIMES'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_type_evenement.edit', $item->id)
            ->with('success', 'TypeEvenement created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = TypeEvenement::findOrFail($id);

        $validated = $request->validate([
            'OLD_TE_CODE' => 'nullable|string|max:255',
            'TE_LIBELLE' => 'nullable|string|max:255',
            'CEV_CODE' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'TE_CODE' => 'nullable|string|max:255',
            'tab' => 'nullable|string|max:255',
            'child' => 'nullable|string|max:255',
            'ope' => 'nullable|string|max:255',
            'icone' => 'nullable|file',
            'suppr' => 'nullable|string|max:255',
            'iconsuppr' => 'nullable|string|max:255',
            'TE_PERSONNEL' => 'nullable|string|max:255',
            'TE_VEHICULES' => 'nullable|string|max:255',
            'TE_MATERIEL' => 'nullable|string|max:255',
            'TE_CONSOMMABLES' => 'nullable|string|max:255',
            'TE_DOCUMENT' => 'nullable|string|max:255',
            'TE_MAP' => 'nullable|string|max:255',
            'CLIENT' => 'nullable|string|max:255',
            'TE_MAIN_COURANTE' => 'nullable|string|max:255',
            'TE_VICTIMES' => 'nullable|string|max:255',
        ]);

        $item->update([
            'OLD_TE_CODE' => $validated['OLD_TE_CODE'] ?? null,
            'TE_LIBELLE' => $validated['TE_LIBELLE'] ?? null,
            'CEV_CODE' => $validated['CEV_CODE'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'TE_CODE' => $validated['TE_CODE'] ?? null,
            'tab' => $validated['tab'] ?? null,
            'child' => $validated['child'] ?? null,
            'ope' => $validated['ope'] ?? null,
            'icone' => $validated['icone'] ?? null,
            'suppr' => $validated['suppr'] ?? null,
            'iconsuppr' => $validated['iconsuppr'] ?? null,
            'TE_PERSONNEL' => $validated['TE_PERSONNEL'] ?? null,
            'TE_VEHICULES' => $validated['TE_VEHICULES'] ?? null,
            'TE_MATERIEL' => $validated['TE_MATERIEL'] ?? null,
            'TE_CONSOMMABLES' => $validated['TE_CONSOMMABLES'] ?? null,
            'TE_DOCUMENT' => $validated['TE_DOCUMENT'] ?? null,
            'TE_MAP' => $validated['TE_MAP'] ?? null,
            'CLIENT' => $validated['CLIENT'] ?? null,
            'TE_MAIN_COURANTE' => $validated['TE_MAIN_COURANTE'] ?? null,
            'TE_VICTIMES' => $validated['TE_VICTIMES'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_type_evenement.edit', $item->id)
            ->with('success', 'TypeEvenement updated successfully');
    }
                

    public function destroy($id)
    {
        $item = TypeEvenement::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.upd_type_evenement.index')
            ->with('success', 'TypeEvenement deleted successfully');
    }
                

    public function applyOperation(Request $request)
    {
        $operation = (string) $request->input('operation');

        if ($operation === 'addstat') {
            return response()->json(['status' => 'ok', 'operation' => 'addstat']);
        }

        if ($operation === 'insert') {
            return response()->json(['status' => 'ok', 'operation' => 'insert']);
        }

        return response()->json(['status' => 'ignored', 'operation' => $operation]);
    }
}
