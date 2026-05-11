<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Qualif2;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_qualif2.php
 * Legacy pattern: save
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class SaveQualif2Controller extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_qualif2.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Qualif2::findOrFail($id);

        return view('legacy_migrated.save_qualif2.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'competence' => 'nullable|string|max:255',
        ]);

        $item = Qualif2::create([
            'competence' => $validated['competence'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_qualif2.edit', $item->id)
            ->with('success', 'Qualif2 created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Qualif2::findOrFail($id);

        $validated = $request->validate([
            'competence' => 'nullable|string|max:255',
        ]);

        $item->update([
            'competence' => $validated['competence'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_qualif2.edit', $item->id)
            ->with('success', 'Qualif2 updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Qualif2::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_qualif2.index')
            ->with('success', 'Qualif2 deleted successfully');
    }
                
}
