<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Responsable;
use Illuminate\Http\Request;

/**
 * Legacy migration source: upd_responsable.php
 * Legacy pattern: edit
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class UpdResponsableController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.upd_responsable.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Responsable::findOrFail($id);

        return view('legacy_migrated.upd_responsable.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'annuler' => 'nullable|string|max:255',
            'sectionresponsable' => 'nullable|string|max:255',
            'resp' => 'nullable|string|max:255',
        ]);

        $item = Responsable::create([
            'sub' => $validated['sub'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
            'sectionresponsable' => $validated['sectionresponsable'] ?? null,
            'resp' => $validated['resp'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_responsable.edit', $item->id)
            ->with('success', 'Responsable created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Responsable::findOrFail($id);

        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'annuler' => 'nullable|string|max:255',
            'sectionresponsable' => 'nullable|string|max:255',
            'resp' => 'nullable|string|max:255',
        ]);

        $item->update([
            'sub' => $validated['sub'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
            'sectionresponsable' => $validated['sectionresponsable'] ?? null,
            'resp' => $validated['resp'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_responsable.edit', $item->id)
            ->with('success', 'Responsable updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Responsable::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.upd_responsable.index')
            ->with('success', 'Responsable deleted successfully');
    }
                
}
