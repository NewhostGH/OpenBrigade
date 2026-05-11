<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Gps;
use Illuminate\Http\Request;

/**
 * Legacy migration source: gps_save.php
 * Legacy pattern: save
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class GpsSaveController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.gps_save.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Gps::findOrFail($id);

        return view('legacy_migrated.gps_save.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pid' => 'nullable|string|max:255',
            'lat' => 'nullable|string|max:255',
            'lng' => 'nullable|string|max:255',
            'findAddress' => 'nullable|string|max:255',
            'GPSAddress' => 'nullable|string|max:255',
        ]);

        $item = Gps::create([
            'pid' => $validated['pid'] ?? null,
            'lat' => $validated['lat'] ?? null,
            'lng' => $validated['lng'] ?? null,
            'findAddress' => $validated['findAddress'] ?? null,
            'GPSAddress' => $validated['GPSAddress'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.gps_save.edit', $item->id)
            ->with('success', 'Gps created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Gps::findOrFail($id);

        $validated = $request->validate([
            'pid' => 'nullable|string|max:255',
            'lat' => 'nullable|string|max:255',
            'lng' => 'nullable|string|max:255',
            'findAddress' => 'nullable|string|max:255',
            'GPSAddress' => 'nullable|string|max:255',
        ]);

        $item->update([
            'pid' => $validated['pid'] ?? null,
            'lat' => $validated['lat'] ?? null,
            'lng' => $validated['lng'] ?? null,
            'findAddress' => $validated['findAddress'] ?? null,
            'GPSAddress' => $validated['GPSAddress'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.gps_save.edit', $item->id)
            ->with('success', 'Gps updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Gps::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.gps_save.index')
            ->with('success', 'Gps deleted successfully');
    }
                
}
