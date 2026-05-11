<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EditCategorieConsommable;
use Illuminate\Http\Request;

/**
 * Legacy migration source: del_edit_categorie_consommable.php
 * Legacy pattern: delete
 * Legacy permission id: 19
 * This file stems from a legacy migration and requires functional verification.
 */
class DelEditCategorieConsommableController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.del_edit_categorie_consommable.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EditCategorieConsommable::findOrFail($id);

        return view('legacy_migrated.del_edit_categorie_consommable.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = EditCategorieConsommable::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_edit_categorie_consommable.edit', $item->id)
            ->with('success', 'EditCategorieConsommable created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EditCategorieConsommable::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_edit_categorie_consommable.edit', $item->id)
            ->with('success', 'EditCategorieConsommable updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EditCategorieConsommable::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.del_edit_categorie_consommable.index')
            ->with('success', 'EditCategorieConsommable deleted successfully');
    }
                
}
