<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EditCategorie;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_edit_categorie.php
 * Legacy pattern: save
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class SaveEditCategorieController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_edit_categorie.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EditCategorie::findOrFail($id);

        return view('legacy_migrated.save_edit_categorie.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'TM_USAGE_PREV' => 'nullable|string|max:255',
            'TM_USAGE' => 'nullable|string|max:255',
            'CM_DESCRIPTION' => 'nullable|string|max:255',
            'logo' => 'nullable|string|max:255',
            'Delete' => 'nullable|string|max:255',
        ]);

        $item = EditCategorie::create([
            'TM_USAGE_PREV' => $validated['TM_USAGE_PREV'] ?? null,
            'TM_USAGE' => $validated['TM_USAGE'] ?? null,
            'CM_DESCRIPTION' => $validated['CM_DESCRIPTION'] ?? null,
            'logo' => $validated['logo'] ?? null,
            'Delete' => $validated['Delete'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_edit_categorie.edit', $item->id)
            ->with('success', 'EditCategorie created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EditCategorie::findOrFail($id);

        $validated = $request->validate([
            'TM_USAGE_PREV' => 'nullable|string|max:255',
            'TM_USAGE' => 'nullable|string|max:255',
            'CM_DESCRIPTION' => 'nullable|string|max:255',
            'logo' => 'nullable|string|max:255',
            'Delete' => 'nullable|string|max:255',
        ]);

        $item->update([
            'TM_USAGE_PREV' => $validated['TM_USAGE_PREV'] ?? null,
            'TM_USAGE' => $validated['TM_USAGE'] ?? null,
            'CM_DESCRIPTION' => $validated['CM_DESCRIPTION'] ?? null,
            'logo' => $validated['logo'] ?? null,
            'Delete' => $validated['Delete'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_edit_categorie.edit', $item->id)
            ->with('success', 'EditCategorie updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EditCategorie::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_edit_categorie.index')
            ->with('success', 'EditCategorie deleted successfully');
    }
                
}
