<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Vehicule;
use Illuminate\Http\Request;

/**
 * Legacy migration source: del_vehicule.php
 * Legacy pattern: delete
 * Legacy permission id: 19
 * This file stems from a legacy migration and requires functional verification.
 */
class DelVehiculeController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.del_vehicule.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Vehicule::findOrFail($id);

        return view('legacy_migrated.del_vehicule.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Vehicule::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_vehicule.edit', $item->id)
            ->with('success', 'Vehicule created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Vehicule::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_vehicule.edit', $item->id)
            ->with('success', 'Vehicule updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Vehicule::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.del_vehicule.index')
            ->with('success', 'Vehicule deleted successfully');
    }
                
}
