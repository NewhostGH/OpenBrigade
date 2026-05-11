<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\PersonnelFormation;
use Illuminate\Http\Request;

/**
 * Legacy migration source: del_personnel_formation.php
 * Legacy pattern: delete
 * Legacy permission id: 4
 * This file stems from a legacy migration and requires functional verification.
 */
class DelPersonnelFormationController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.del_personnel_formation.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = PersonnelFormation::findOrFail($id);

        return view('legacy_migrated.del_personnel_formation.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = PersonnelFormation::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_personnel_formation.edit', $item->id)
            ->with('success', 'PersonnelFormation created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = PersonnelFormation::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_personnel_formation.edit', $item->id)
            ->with('success', 'PersonnelFormation updated successfully');
    }
                

    public function destroy($id)
    {
        $item = PersonnelFormation::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.del_personnel_formation.index')
            ->with('success', 'PersonnelFormation deleted successfully');
    }
                
}
