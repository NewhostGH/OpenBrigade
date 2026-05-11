<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\PersonnelPreferences;
use Illuminate\Http\Request;

/**
 * Legacy migration source: personnel_preferences.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class PersonnelPreferencesController extends Controller
{
    public function index(Request $request)
    {
        $query = PersonnelPreferences::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
                $query->orWhere('p_section', 'like', '%' . $term . '%');
                $query->orWhere('p_favorite_section', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.personnel_preferences.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.personnel_preferences.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = PersonnelPreferences::findOrFail($id);

        return view('legacy_migrated.personnel_preferences.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            '$chkname' => 'nullable|string|max:255',
            'pid' => 'nullable|string|max:255',
            'f$ID' => 'nullable|string|max:255',
            'switchstats' => 'nullable|string|max:255',
            'end' => 'nullable|string|max:255',
            'U' => 'nullable|string|max:255',
            'F' => 'nullable|string|max:255',
            'prefCalend' => 'nullable|string|max:255',
        ]);

        $item = PersonnelPreferences::create([
            '$chkname' => $validated['$chkname'] ?? null,
            'pid' => $validated['pid'] ?? null,
            'f$ID' => $validated['f$ID'] ?? null,
            'switchstats' => $validated['switchstats'] ?? null,
            'end' => $validated['end'] ?? null,
            'U' => $validated['U'] ?? null,
            'F' => $validated['F'] ?? null,
            'prefCalend' => $validated['prefCalend'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.personnel_preferences.edit', $item->id)
            ->with('success', 'PersonnelPreferences created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = PersonnelPreferences::findOrFail($id);

        $validated = $request->validate([
            '$chkname' => 'nullable|string|max:255',
            'pid' => 'nullable|string|max:255',
            'f$ID' => 'nullable|string|max:255',
            'switchstats' => 'nullable|string|max:255',
            'end' => 'nullable|string|max:255',
            'U' => 'nullable|string|max:255',
            'F' => 'nullable|string|max:255',
            'prefCalend' => 'nullable|string|max:255',
        ]);

        $item->update([
            '$chkname' => $validated['$chkname'] ?? null,
            'pid' => $validated['pid'] ?? null,
            'f$ID' => $validated['f$ID'] ?? null,
            'switchstats' => $validated['switchstats'] ?? null,
            'end' => $validated['end'] ?? null,
            'U' => $validated['U'] ?? null,
            'F' => $validated['F'] ?? null,
            'prefCalend' => $validated['prefCalend'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.personnel_preferences.edit', $item->id)
            ->with('success', 'PersonnelPreferences updated successfully');
    }
                

    public function destroy($id)
    {
        $item = PersonnelPreferences::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.personnel_preferences.index')
            ->with('success', 'PersonnelPreferences deleted successfully');
    }
                
}
