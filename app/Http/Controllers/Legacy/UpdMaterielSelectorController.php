<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\MaterielSelector;
use Illuminate\Http\Request;

/**
 * Legacy migration source: upd_materiel_selector.php
 * Legacy pattern: edit
 * Legacy permission id: 42
 * This file stems from a legacy migration and requires functional verification.
 */
class UpdMaterielSelectorController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.upd_materiel_selector.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = MaterielSelector::findOrFail($id);

        return view('legacy_migrated.upd_materiel_selector.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'TV_ID' => 'nullable|string|max:255',
        ]);

        $item = MaterielSelector::create([
            'TV_ID' => $validated['TV_ID'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_materiel_selector.edit', $item->id)
            ->with('success', 'MaterielSelector created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = MaterielSelector::findOrFail($id);

        $validated = $request->validate([
            'TV_ID' => 'nullable|string|max:255',
        ]);

        $item->update([
            'TV_ID' => $validated['TV_ID'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_materiel_selector.edit', $item->id)
            ->with('success', 'MaterielSelector updated successfully');
    }
                

    public function destroy($id)
    {
        $item = MaterielSelector::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.upd_materiel_selector.index')
            ->with('success', 'MaterielSelector deleted successfully');
    }
                
}
