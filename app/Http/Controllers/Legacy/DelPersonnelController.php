<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Personnel;
use Illuminate\Http\Request;

/**
 * Legacy migration source: del_personnel.php
 * Legacy pattern: delete
 * Legacy permission id: 3
 * This file stems from a legacy migration and requires functional verification.
 */
class DelPersonnelController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.del_personnel.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Personnel::findOrFail($id);

        return view('legacy_migrated.del_personnel.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Personnel::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_personnel.edit', $item->id)
            ->with('success', 'Personnel created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Personnel::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_personnel.edit', $item->id)
            ->with('success', 'Personnel updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Personnel::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.del_personnel.index')
            ->with('success', 'Personnel deleted successfully');
    }
                
}
