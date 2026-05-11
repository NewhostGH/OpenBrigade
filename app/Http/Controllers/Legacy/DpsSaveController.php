<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Dps;
use Illuminate\Http\Request;

/**
 * Legacy migration source: dps_save.php
 * Legacy pattern: save
 * Legacy permission id: 15
 * This file stems from a legacy migration and requires functional verification.
 */
class DpsSaveController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.dps_save.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Dps::findOrFail($id);

        return view('legacy_migrated.dps_save.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'evenement' => 'nullable|string|max:255',
            'P1' => 'nullable|string|max:255',
            'P2' => 'nullable|string|max:255',
            'E1' => 'nullable|string|max:255',
            'E2' => 'nullable|string|max:255',
            'dimNbISActeurs' => 'nullable|string|max:255',
            'dimNbISActeursCom' => 'nullable|string|max:255',
        ]);

        $item = Dps::create([
            'evenement' => $validated['evenement'] ?? null,
            'P1' => $validated['P1'] ?? null,
            'P2' => $validated['P2'] ?? null,
            'E1' => $validated['E1'] ?? null,
            'E2' => $validated['E2'] ?? null,
            'dimNbISActeurs' => $validated['dimNbISActeurs'] ?? null,
            'dimNbISActeursCom' => $validated['dimNbISActeursCom'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.dps_save.edit', $item->id)
            ->with('success', 'Dps created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Dps::findOrFail($id);

        $validated = $request->validate([
            'evenement' => 'nullable|string|max:255',
            'P1' => 'nullable|string|max:255',
            'P2' => 'nullable|string|max:255',
            'E1' => 'nullable|string|max:255',
            'E2' => 'nullable|string|max:255',
            'dimNbISActeurs' => 'nullable|string|max:255',
            'dimNbISActeursCom' => 'nullable|string|max:255',
        ]);

        $item->update([
            'evenement' => $validated['evenement'] ?? null,
            'P1' => $validated['P1'] ?? null,
            'P2' => $validated['P2'] ?? null,
            'E1' => $validated['E1'] ?? null,
            'E2' => $validated['E2'] ?? null,
            'dimNbISActeurs' => $validated['dimNbISActeurs'] ?? null,
            'dimNbISActeursCom' => $validated['dimNbISActeursCom'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.dps_save.edit', $item->id)
            ->with('success', 'Dps updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Dps::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.dps_save.index')
            ->with('success', 'Dps deleted successfully');
    }
                
}
