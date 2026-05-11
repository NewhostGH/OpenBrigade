<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Poste;
use Illuminate\Http\Request;

/**
 * Legacy migration source: del_poste.php
 * Legacy pattern: delete
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class DelPosteController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.del_poste.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Poste::findOrFail($id);

        return view('legacy_migrated.del_poste.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Poste::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_poste.edit', $item->id)
            ->with('success', 'Poste created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Poste::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_poste.edit', $item->id)
            ->with('success', 'Poste updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Poste::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.del_poste.index')
            ->with('success', 'Poste deleted successfully');
    }
                
}
