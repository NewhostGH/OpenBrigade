<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Accueil;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_accueil.php
 * Legacy pattern: save
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class SaveAccueilController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_accueil.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Accueil::findOrFail($id);

        return view('legacy_migrated.save_accueil.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pid' => 'nullable|string|max:255',
            'wid' => 'nullable|string|max:255',
            'zone' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'show' => 'nullable|string|max:255',
        ]);

        $item = Accueil::create([
            'pid' => $validated['pid'] ?? null,
            'wid' => $validated['wid'] ?? null,
            'zone' => $validated['zone'] ?? null,
            'position' => $validated['position'] ?? null,
            'show' => $validated['show'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_accueil.edit', $item->id)
            ->with('success', 'Accueil created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Accueil::findOrFail($id);

        $validated = $request->validate([
            'pid' => 'nullable|string|max:255',
            'wid' => 'nullable|string|max:255',
            'zone' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'show' => 'nullable|string|max:255',
        ]);

        $item->update([
            'pid' => $validated['pid'] ?? null,
            'wid' => $validated['wid'] ?? null,
            'zone' => $validated['zone'] ?? null,
            'position' => $validated['position'] ?? null,
            'show' => $validated['show'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_accueil.edit', $item->id)
            ->with('success', 'Accueil updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Accueil::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_accueil.index')
            ->with('success', 'Accueil deleted successfully');
    }
                
}
