<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\TypeConsommable;
use Illuminate\Http\Request;

/**
 * Legacy migration source: del_type_consommable.php
 * Legacy pattern: delete
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class DelTypeConsommableController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.del_type_consommable.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = TypeConsommable::findOrFail($id);

        return view('legacy_migrated.del_type_consommable.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = TypeConsommable::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_type_consommable.edit', $item->id)
            ->with('success', 'TypeConsommable created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = TypeConsommable::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_type_consommable.edit', $item->id)
            ->with('success', 'TypeConsommable updated successfully');
    }
                

    public function destroy($id)
    {
        $item = TypeConsommable::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.del_type_consommable.index')
            ->with('success', 'TypeConsommable deleted successfully');
    }
                
}
