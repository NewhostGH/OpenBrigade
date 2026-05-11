<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Addons;
use Illuminate\Http\Request;

/**
 * Legacy migration source: addons_save.php
 * Legacy pattern: save
 * Legacy permission id: 78
 * This file stems from a legacy migration and requires functional verification.
 */
class AddonsSaveController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.addons_save.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Addons::findOrFail($id);

        return view('legacy_migrated.addons_save.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Addons::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.addons_save.edit', $item->id)
            ->with('success', 'Addons created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Addons::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.addons_save.edit', $item->id)
            ->with('success', 'Addons updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Addons::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.addons_save.index')
            ->with('success', 'Addons deleted successfully');
    }
                
}
