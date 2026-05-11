<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Indispo;
use Illuminate\Http\Request;

/**
 * Legacy migration source: indispo_save.php
 * Legacy pattern: save
 * Legacy permission id: 11
 * This file stems from a legacy migration and requires functional verification.
 */
class IndispoSaveController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.indispo_save.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Indispo::findOrFail($id);

        return view('legacy_migrated.indispo_save.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Indispo::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.indispo_save.edit', $item->id)
            ->with('success', 'Indispo created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Indispo::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.indispo_save.edit', $item->id)
            ->with('success', 'Indispo updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Indispo::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.indispo_save.index')
            ->with('success', 'Indispo deleted successfully');
    }
                
}
