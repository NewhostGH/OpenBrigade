<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\PersonnelTenues;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_personnel_tenues.php
 * Legacy pattern: save
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class SavePersonnelTenuesController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_personnel_tenues.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = PersonnelTenues::findOrFail($id);

        return view('legacy_migrated.save_personnel_tenues.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pompier' => 'nullable|string|max:255',
        ]);

        $item = PersonnelTenues::create([
            'pompier' => $validated['pompier'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_personnel_tenues.edit', $item->id)
            ->with('success', 'PersonnelTenues created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = PersonnelTenues::findOrFail($id);

        $validated = $request->validate([
            'pompier' => 'nullable|string|max:255',
        ]);

        $item->update([
            'pompier' => $validated['pompier'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_personnel_tenues.edit', $item->id)
            ->with('success', 'PersonnelTenues updated successfully');
    }
                

    public function destroy($id)
    {
        $item = PersonnelTenues::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_personnel_tenues.index')
            ->with('success', 'PersonnelTenues deleted successfully');
    }
                
}
