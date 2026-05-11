<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Materiel;
use Illuminate\Http\Request;

/**
 * Legacy migration source: del_materiel.php
 * Legacy pattern: delete
 * Legacy permission id: 70
 * This file stems from a legacy migration and requires functional verification.
 */
class DelMaterielController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.del_materiel.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Materiel::findOrFail($id);

        return view('legacy_migrated.del_materiel.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Materiel::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_materiel.edit', $item->id)
            ->with('success', 'Materiel created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Materiel::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_materiel.edit', $item->id)
            ->with('success', 'Materiel updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Materiel::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.del_materiel.index')
            ->with('success', 'Materiel deleted successfully');
    }
                
}
