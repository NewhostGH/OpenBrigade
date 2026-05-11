<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Gps2;
use Illuminate\Http\Request;

/**
 * Legacy migration source: gps_save2.php
 * Legacy pattern: generic
 * Legacy permission id: 76
 * This file stems from a legacy migration and requires functional verification.
 */
class GpsSave2Controller extends Controller
{
    public function index(Request $request)
    {
        $query = Gps2::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.gps_save2.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.gps_save2.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Gps2::findOrFail($id);

        return view('legacy_migrated.gps_save2.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'zoomlevel' => 'nullable|string|max:255',
            'maptypeid' => 'nullable|string|max:255',
            'centerlat' => 'nullable|string|max:255',
            'centerlng' => 'nullable|string|max:255',
        ]);

        $item = Gps2::create([
            'zoomlevel' => $validated['zoomlevel'] ?? null,
            'maptypeid' => $validated['maptypeid'] ?? null,
            'centerlat' => $validated['centerlat'] ?? null,
            'centerlng' => $validated['centerlng'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.gps_save2.edit', $item->id)
            ->with('success', 'Gps2 created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Gps2::findOrFail($id);

        $validated = $request->validate([
            'zoomlevel' => 'nullable|string|max:255',
            'maptypeid' => 'nullable|string|max:255',
            'centerlat' => 'nullable|string|max:255',
            'centerlng' => 'nullable|string|max:255',
        ]);

        $item->update([
            'zoomlevel' => $validated['zoomlevel'] ?? null,
            'maptypeid' => $validated['maptypeid'] ?? null,
            'centerlat' => $validated['centerlat'] ?? null,
            'centerlng' => $validated['centerlng'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.gps_save2.edit', $item->id)
            ->with('success', 'Gps2 updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Gps2::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.gps_save2.index')
            ->with('success', 'Gps2 deleted successfully');
    }
                
}
