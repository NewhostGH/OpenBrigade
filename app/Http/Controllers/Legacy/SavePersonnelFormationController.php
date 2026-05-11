<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\PersonnelFormation;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_personnel_formation.php
 * Legacy pattern: save
 * Legacy permission id: 4
 * This file stems from a legacy migration and requires functional verification.
 */
class SavePersonnelFormationController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_personnel_formation.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = PersonnelFormation::findOrFail($id);

        return view('legacy_migrated.save_personnel_formation.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'P_ID' => 'nullable|string|max:255',
            'PS_ID' => 'nullable|string|max:255',
            'PF_ID' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'tf' => 'nullable|string|max:255',
            'dc' => 'nullable|string|max:255',
            'lieu' => 'nullable|string|max:255',
            'resp' => 'nullable|string|max:255',
            'numdiplome' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:255',
        ]);

        $item = PersonnelFormation::create([
            'P_ID' => $validated['P_ID'] ?? null,
            'PS_ID' => $validated['PS_ID'] ?? null,
            'PF_ID' => $validated['PF_ID'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'tf' => $validated['tf'] ?? null,
            'dc' => $validated['dc'] ?? null,
            'lieu' => $validated['lieu'] ?? null,
            'resp' => $validated['resp'] ?? null,
            'numdiplome' => $validated['numdiplome'] ?? null,
            'comment' => $validated['comment'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_personnel_formation.edit', $item->id)
            ->with('success', 'PersonnelFormation created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = PersonnelFormation::findOrFail($id);

        $validated = $request->validate([
            'P_ID' => 'nullable|string|max:255',
            'PS_ID' => 'nullable|string|max:255',
            'PF_ID' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'tf' => 'nullable|string|max:255',
            'dc' => 'nullable|string|max:255',
            'lieu' => 'nullable|string|max:255',
            'resp' => 'nullable|string|max:255',
            'numdiplome' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:255',
        ]);

        $item->update([
            'P_ID' => $validated['P_ID'] ?? null,
            'PS_ID' => $validated['PS_ID'] ?? null,
            'PF_ID' => $validated['PF_ID'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'tf' => $validated['tf'] ?? null,
            'dc' => $validated['dc'] ?? null,
            'lieu' => $validated['lieu'] ?? null,
            'resp' => $validated['resp'] ?? null,
            'numdiplome' => $validated['numdiplome'] ?? null,
            'comment' => $validated['comment'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_personnel_formation.edit', $item->id)
            ->with('success', 'PersonnelFormation updated successfully');
    }
                

    public function destroy($id)
    {
        $item = PersonnelFormation::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_personnel_formation.index')
            ->with('success', 'PersonnelFormation deleted successfully');
    }
                
}
