<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Groupe;
use Illuminate\Http\Request;

/**
 * Legacy migration source: del_groupe.php
 * Legacy pattern: delete
 * Legacy permission id: 9
 * This file stems from a legacy migration and requires functional verification.
 */
class DelGroupeController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.del_groupe.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Groupe::findOrFail($id);

        return view('legacy_migrated.del_groupe.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Groupe::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_groupe.edit', $item->id)
            ->with('success', 'Groupe created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Groupe::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_groupe.edit', $item->id)
            ->with('success', 'Groupe updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Groupe::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.del_groupe.index')
            ->with('success', 'Groupe deleted successfully');
    }
                
}
