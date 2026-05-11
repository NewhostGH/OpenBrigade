<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Astreinte;
use Illuminate\Http\Request;

/**
 * Legacy migration source: astreinte_save.php
 * Legacy pattern: save
 * Legacy permission id: 26
 * This file stems from a legacy migration and requires functional verification.
 */
class AstreinteSaveController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.astreinte_save.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Astreinte::findOrFail($id);

        return view('legacy_migrated.astreinte_save.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'section' => 'nullable|string|max:255',
            'person' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'dc1' => 'nullable|string|max:255',
            'dc2' => 'nullable|string|max:255',
            'astreinte' => 'nullable|string|max:255',
        ]);

        $item = Astreinte::create([
            'section' => $validated['section'] ?? null,
            'person' => $validated['person'] ?? null,
            'type' => $validated['type'] ?? null,
            'dc1' => $validated['dc1'] ?? null,
            'dc2' => $validated['dc2'] ?? null,
            'astreinte' => $validated['astreinte'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.astreinte_save.edit', $item->id)
            ->with('success', 'Astreinte created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Astreinte::findOrFail($id);

        $validated = $request->validate([
            'section' => 'nullable|string|max:255',
            'person' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'dc1' => 'nullable|string|max:255',
            'dc2' => 'nullable|string|max:255',
            'astreinte' => 'nullable|string|max:255',
        ]);

        $item->update([
            'section' => $validated['section'] ?? null,
            'person' => $validated['person'] ?? null,
            'type' => $validated['type'] ?? null,
            'dc1' => $validated['dc1'] ?? null,
            'dc2' => $validated['dc2'] ?? null,
            'astreinte' => $validated['astreinte'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.astreinte_save.edit', $item->id)
            ->with('success', 'Astreinte updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Astreinte::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.astreinte_save.index')
            ->with('success', 'Astreinte deleted successfully');
    }
                
}
