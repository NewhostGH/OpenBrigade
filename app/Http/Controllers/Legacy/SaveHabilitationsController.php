<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Habilitations;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_habilitations.php
 * Legacy pattern: save
 * Legacy permission id: 9
 * This file stems from a legacy migration and requires functional verification.
 */
class SaveHabilitationsController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_habilitations.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Habilitations::findOrFail($id);

        return view('legacy_migrated.save_habilitations.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Habilitations::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.save_habilitations.edit', $item->id)
            ->with('success', 'Habilitations created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Habilitations::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.save_habilitations.edit', $item->id)
            ->with('success', 'Habilitations updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Habilitations::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_habilitations.index')
            ->with('success', 'Habilitations deleted successfully');
    }
                
}
