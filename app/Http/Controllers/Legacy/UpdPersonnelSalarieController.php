<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\PersonnelSalarie;
use Illuminate\Http\Request;

/**
 * Legacy migration source: upd_personnel_salarie.php
 * Legacy pattern: edit
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class UpdPersonnelSalarieController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.upd_personnel_salarie.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = PersonnelSalarie::findOrFail($id);

        return view('legacy_migrated.upd_personnel_salarie.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'person' => 'nullable|string|max:255',
            'heures' => 'nullable|string|max:255',
            'heures_par_jour' => 'nullable|string|max:255',
            'heures_par_an' => 'nullable|string|max:255',
            'heures_a_recuperer' => 'nullable|string|max:255',
            'cp_par_an' => 'nullable|string|max:255',
            'reliquat_cp' => 'nullable|string|max:255',
            'reliquat_rtt' => 'nullable|string|max:255',
        ]);

        $item = PersonnelSalarie::create([
            'person' => $validated['person'] ?? null,
            'heures' => $validated['heures'] ?? null,
            'heures_par_jour' => $validated['heures_par_jour'] ?? null,
            'heures_par_an' => $validated['heures_par_an'] ?? null,
            'heures_a_recuperer' => $validated['heures_a_recuperer'] ?? null,
            'cp_par_an' => $validated['cp_par_an'] ?? null,
            'reliquat_cp' => $validated['reliquat_cp'] ?? null,
            'reliquat_rtt' => $validated['reliquat_rtt'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_personnel_salarie.edit', $item->id)
            ->with('success', 'PersonnelSalarie created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = PersonnelSalarie::findOrFail($id);

        $validated = $request->validate([
            'person' => 'nullable|string|max:255',
            'heures' => 'nullable|string|max:255',
            'heures_par_jour' => 'nullable|string|max:255',
            'heures_par_an' => 'nullable|string|max:255',
            'heures_a_recuperer' => 'nullable|string|max:255',
            'cp_par_an' => 'nullable|string|max:255',
            'reliquat_cp' => 'nullable|string|max:255',
            'reliquat_rtt' => 'nullable|string|max:255',
        ]);

        $item->update([
            'person' => $validated['person'] ?? null,
            'heures' => $validated['heures'] ?? null,
            'heures_par_jour' => $validated['heures_par_jour'] ?? null,
            'heures_par_an' => $validated['heures_par_an'] ?? null,
            'heures_a_recuperer' => $validated['heures_a_recuperer'] ?? null,
            'cp_par_an' => $validated['cp_par_an'] ?? null,
            'reliquat_cp' => $validated['reliquat_cp'] ?? null,
            'reliquat_rtt' => $validated['reliquat_rtt'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_personnel_salarie.edit', $item->id)
            ->with('success', 'PersonnelSalarie updated successfully');
    }
                

    public function destroy($id)
    {
        $item = PersonnelSalarie::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.upd_personnel_salarie.index')
            ->with('success', 'PersonnelSalarie deleted successfully');
    }
                
}
