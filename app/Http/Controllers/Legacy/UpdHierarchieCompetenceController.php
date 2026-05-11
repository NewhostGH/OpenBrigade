<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\HierarchieCompetence;
use Illuminate\Http\Request;

/**
 * Legacy migration source: upd_hierarchie_competence.php
 * Legacy pattern: edit
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class UpdHierarchieCompetenceController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.upd_hierarchie_competence.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = HierarchieCompetence::findOrFail($id);

        return view('legacy_migrated.upd_hierarchie_competence.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'OLD_PH_CODE' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'PH_CODE' => 'nullable|string|max:255',
            'PH_NAME' => 'nullable|string|max:255',
            'PH_HIDE_LOWER' => 'nullable|string|max:255',
            'PH_UPDATE_LOWER_EXPIRY' => 'nullable|string|max:255',
            'PH_UPDATE_MANDATORY' => 'nullable|string|max:255',
            'annuler' => 'nullable|string|max:255',
        ]);

        $item = HierarchieCompetence::create([
            'OLD_PH_CODE' => $validated['OLD_PH_CODE'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'PH_CODE' => $validated['PH_CODE'] ?? null,
            'PH_NAME' => $validated['PH_NAME'] ?? null,
            'PH_HIDE_LOWER' => $validated['PH_HIDE_LOWER'] ?? null,
            'PH_UPDATE_LOWER_EXPIRY' => $validated['PH_UPDATE_LOWER_EXPIRY'] ?? null,
            'PH_UPDATE_MANDATORY' => $validated['PH_UPDATE_MANDATORY'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_hierarchie_competence.edit', $item->id)
            ->with('success', 'HierarchieCompetence created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = HierarchieCompetence::findOrFail($id);

        $validated = $request->validate([
            'OLD_PH_CODE' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'PH_CODE' => 'nullable|string|max:255',
            'PH_NAME' => 'nullable|string|max:255',
            'PH_HIDE_LOWER' => 'nullable|string|max:255',
            'PH_UPDATE_LOWER_EXPIRY' => 'nullable|string|max:255',
            'PH_UPDATE_MANDATORY' => 'nullable|string|max:255',
            'annuler' => 'nullable|string|max:255',
        ]);

        $item->update([
            'OLD_PH_CODE' => $validated['OLD_PH_CODE'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'PH_CODE' => $validated['PH_CODE'] ?? null,
            'PH_NAME' => $validated['PH_NAME'] ?? null,
            'PH_HIDE_LOWER' => $validated['PH_HIDE_LOWER'] ?? null,
            'PH_UPDATE_LOWER_EXPIRY' => $validated['PH_UPDATE_LOWER_EXPIRY'] ?? null,
            'PH_UPDATE_MANDATORY' => $validated['PH_UPDATE_MANDATORY'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_hierarchie_competence.edit', $item->id)
            ->with('success', 'HierarchieCompetence updated successfully');
    }
                

    public function destroy($id)
    {
        $item = HierarchieCompetence::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.upd_hierarchie_competence.index')
            ->with('success', 'HierarchieCompetence deleted successfully');
    }
                
}
