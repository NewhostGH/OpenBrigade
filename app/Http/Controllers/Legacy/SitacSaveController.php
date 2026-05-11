<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Sitac;
use Illuminate\Http\Request;

/**
 * Legacy migration source: sitac_save.php
 * Legacy pattern: save
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class SitacSaveController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.sitac_save.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Sitac::findOrFail($id);

        return view('legacy_migrated.sitac_save.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'evenement' => 'nullable|string|max:255',
            'centerlat' => 'nullable|string|max:255',
            'centerlng' => 'nullable|string|max:255',
            'zoomlevel' => 'nullable|string|max:255',
            'maptypeid' => 'nullable|string|max:255',
            'custom' => 'nullable|string|max:255',
            'latitude' => 'nullable|string|max:255',
            'longitude' => 'nullable|string|max:255',
            'flag' => 'nullable|string|max:255',
            'equipe' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'intervention' => 'nullable|string|max:255',
            'cav' => 'nullable|string|max:255',
        ]);

        $item = Sitac::create([
            'evenement' => $validated['evenement'] ?? null,
            'centerlat' => $validated['centerlat'] ?? null,
            'centerlng' => $validated['centerlng'] ?? null,
            'zoomlevel' => $validated['zoomlevel'] ?? null,
            'maptypeid' => $validated['maptypeid'] ?? null,
            'custom' => $validated['custom'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'flag' => $validated['flag'] ?? null,
            'equipe' => $validated['equipe'] ?? null,
            'address' => $validated['address'] ?? null,
            'status' => $validated['status'] ?? null,
            'intervention' => $validated['intervention'] ?? null,
            'cav' => $validated['cav'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.sitac_save.edit', $item->id)
            ->with('success', 'Sitac created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Sitac::findOrFail($id);

        $validated = $request->validate([
            'evenement' => 'nullable|string|max:255',
            'centerlat' => 'nullable|string|max:255',
            'centerlng' => 'nullable|string|max:255',
            'zoomlevel' => 'nullable|string|max:255',
            'maptypeid' => 'nullable|string|max:255',
            'custom' => 'nullable|string|max:255',
            'latitude' => 'nullable|string|max:255',
            'longitude' => 'nullable|string|max:255',
            'flag' => 'nullable|string|max:255',
            'equipe' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'intervention' => 'nullable|string|max:255',
            'cav' => 'nullable|string|max:255',
        ]);

        $item->update([
            'evenement' => $validated['evenement'] ?? null,
            'centerlat' => $validated['centerlat'] ?? null,
            'centerlng' => $validated['centerlng'] ?? null,
            'zoomlevel' => $validated['zoomlevel'] ?? null,
            'maptypeid' => $validated['maptypeid'] ?? null,
            'custom' => $validated['custom'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'flag' => $validated['flag'] ?? null,
            'equipe' => $validated['equipe'] ?? null,
            'address' => $validated['address'] ?? null,
            'status' => $validated['status'] ?? null,
            'intervention' => $validated['intervention'] ?? null,
            'cav' => $validated['cav'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.sitac_save.edit', $item->id)
            ->with('success', 'Sitac updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Sitac::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.sitac_save.index')
            ->with('success', 'Sitac deleted successfully');
    }
                
}
