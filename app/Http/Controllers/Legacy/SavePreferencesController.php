<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Preferences;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_preferences.php
 * Legacy pattern: save
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class SavePreferencesController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_preferences.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Preferences::findOrFail($id);

        return view('legacy_migrated.save_preferences.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pid' => 'nullable|string|max:255',
        ]);

        $item = Preferences::create([
            'pid' => $validated['pid'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_preferences.edit', $item->id)
            ->with('success', 'Preferences created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Preferences::findOrFail($id);

        $validated = $request->validate([
            'pid' => 'nullable|string|max:255',
        ]);

        $item->update([
            'pid' => $validated['pid'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_preferences.edit', $item->id)
            ->with('success', 'Preferences updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Preferences::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_preferences.index')
            ->with('success', 'Preferences deleted successfully');
    }
                
}
