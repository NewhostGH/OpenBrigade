<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EditCategorieConsommable;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_edit_categorie_consommable.php
 * Legacy pattern: save
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class SaveEditCategorieConsommableController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_edit_categorie_consommable.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EditCategorieConsommable::findOrFail($id);

        return view('legacy_migrated.save_edit_categorie_consommable.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'CC_CODE_PREV' => 'nullable|string|max:255',
            'CC_CODE' => 'nullable|string|max:255',
            'CC_NAME' => 'nullable|string|max:255',
            'logo' => 'nullable|string|max:255',
            'CC_DESCRIPTION' => 'nullable|string|max:255',
            'Delete' => 'nullable|string|max:255',
        ]);

        $item = EditCategorieConsommable::create([
            'CC_CODE_PREV' => $validated['CC_CODE_PREV'] ?? null,
            'CC_CODE' => $validated['CC_CODE'] ?? null,
            'CC_NAME' => $validated['CC_NAME'] ?? null,
            'logo' => $validated['logo'] ?? null,
            'CC_DESCRIPTION' => $validated['CC_DESCRIPTION'] ?? null,
            'Delete' => $validated['Delete'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_edit_categorie_consommable.edit', $item->id)
            ->with('success', 'EditCategorieConsommable created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EditCategorieConsommable::findOrFail($id);

        $validated = $request->validate([
            'CC_CODE_PREV' => 'nullable|string|max:255',
            'CC_CODE' => 'nullable|string|max:255',
            'CC_NAME' => 'nullable|string|max:255',
            'logo' => 'nullable|string|max:255',
            'CC_DESCRIPTION' => 'nullable|string|max:255',
            'Delete' => 'nullable|string|max:255',
        ]);

        $item->update([
            'CC_CODE_PREV' => $validated['CC_CODE_PREV'] ?? null,
            'CC_CODE' => $validated['CC_CODE'] ?? null,
            'CC_NAME' => $validated['CC_NAME'] ?? null,
            'logo' => $validated['logo'] ?? null,
            'CC_DESCRIPTION' => $validated['CC_DESCRIPTION'] ?? null,
            'Delete' => $validated['Delete'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_edit_categorie_consommable.edit', $item->id)
            ->with('success', 'EditCategorieConsommable updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EditCategorieConsommable::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_edit_categorie_consommable.index')
            ->with('success', 'EditCategorieConsommable deleted successfully');
    }
                
}
