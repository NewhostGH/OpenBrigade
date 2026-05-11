<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Consommable;
use Illuminate\Http\Request;

/**
 * Legacy migration source: del_consommable.php
 * Legacy pattern: delete
 * Legacy permission id: 71
 * This file stems from a legacy migration and requires functional verification.
 */
class DelConsommableController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.del_consommable.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Consommable::findOrFail($id);

        return view('legacy_migrated.del_consommable.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Consommable::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_consommable.edit', $item->id)
            ->with('success', 'Consommable created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Consommable::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_consommable.edit', $item->id)
            ->with('success', 'Consommable updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Consommable::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.del_consommable.index')
            ->with('success', 'Consommable deleted successfully');
    }
                
}
