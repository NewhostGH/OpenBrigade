<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Qualif;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_qualif.php
 * Legacy pattern: save
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class SaveQualifController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_qualif.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Qualif::findOrFail($id);

        return view('legacy_migrated.save_qualif.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pompier' => 'nullable|string|max:255',
            'from' => 'nullable|string|max:255',
        ]);

        $item = Qualif::create([
            'pompier' => $validated['pompier'] ?? null,
            'from' => $validated['from'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_qualif.edit', $item->id)
            ->with('success', 'Qualif created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Qualif::findOrFail($id);

        $validated = $request->validate([
            'pompier' => 'nullable|string|max:255',
            'from' => 'nullable|string|max:255',
        ]);

        $item->update([
            'pompier' => $validated['pompier'] ?? null,
            'from' => $validated['from'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_qualif.edit', $item->id)
            ->with('success', 'Qualif updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Qualif::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_qualif.index')
            ->with('success', 'Qualif deleted successfully');
    }
                
}
