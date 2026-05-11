<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EditCategorie;
use Illuminate\Http\Request;

/**
 * Legacy migration source: del_edit_categorie.php
 * Legacy pattern: delete
 * Legacy permission id: 19
 * This file stems from a legacy migration and requires functional verification.
 */
class DelEditCategorieController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.del_edit_categorie.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EditCategorie::findOrFail($id);

        return view('legacy_migrated.del_edit_categorie.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = EditCategorie::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_edit_categorie.edit', $item->id)
            ->with('success', 'EditCategorie created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EditCategorie::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_edit_categorie.edit', $item->id)
            ->with('success', 'EditCategorie updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EditCategorie::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.del_edit_categorie.index')
            ->with('success', 'EditCategorie deleted successfully');
    }
                
}
