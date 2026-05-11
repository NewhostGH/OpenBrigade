<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Consommable;
use Illuminate\Http\Request;

/**
 * Legacy migration source: upd_consommable.php
 * Legacy pattern: edit
 * Legacy permission id: 42
 * This file stems from a legacy migration and requires functional verification.
 */
class UpdConsommableController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.upd_consommable.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Consommable::findOrFail($id);

        return view('legacy_migrated.upd_consommable.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'C_ID' => 'nullable|string|max:255',
            'numlot' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'quantity' => 'nullable|string|max:255',
            'minimum' => 'nullable|string|max:255',
            'C_DESCRIPTION' => 'nullable|string|max:255',
            'dc0' => 'nullable|string|max:255',
            'C_DATE_ACHAT' => 'nullable|string|max:255',
            'C_DATE_PEREMPTION' => 'nullable|string|max:255',
            'C_LIEU_STOCKAGE' => 'nullable|string|max:255',
            'S_ID' => 'nullable|string|max:255',
            'annuler' => 'nullable|string|max:255',
            'TC_ID' => 'nullable|string|max:255',
        ]);

        $item = Consommable::create([
            'C_ID' => $validated['C_ID'] ?? null,
            'numlot' => $validated['numlot'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'quantity' => $validated['quantity'] ?? null,
            'minimum' => $validated['minimum'] ?? null,
            'C_DESCRIPTION' => $validated['C_DESCRIPTION'] ?? null,
            'dc0' => $validated['dc0'] ?? null,
            'C_DATE_ACHAT' => $validated['C_DATE_ACHAT'] ?? null,
            'C_DATE_PEREMPTION' => $validated['C_DATE_PEREMPTION'] ?? null,
            'C_LIEU_STOCKAGE' => $validated['C_LIEU_STOCKAGE'] ?? null,
            'S_ID' => $validated['S_ID'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
            'TC_ID' => $validated['TC_ID'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_consommable.edit', $item->id)
            ->with('success', 'Consommable created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Consommable::findOrFail($id);

        $validated = $request->validate([
            'C_ID' => 'nullable|string|max:255',
            'numlot' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'quantity' => 'nullable|string|max:255',
            'minimum' => 'nullable|string|max:255',
            'C_DESCRIPTION' => 'nullable|string|max:255',
            'dc0' => 'nullable|string|max:255',
            'C_DATE_ACHAT' => 'nullable|string|max:255',
            'C_DATE_PEREMPTION' => 'nullable|string|max:255',
            'C_LIEU_STOCKAGE' => 'nullable|string|max:255',
            'S_ID' => 'nullable|string|max:255',
            'annuler' => 'nullable|string|max:255',
            'TC_ID' => 'nullable|string|max:255',
        ]);

        $item->update([
            'C_ID' => $validated['C_ID'] ?? null,
            'numlot' => $validated['numlot'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'quantity' => $validated['quantity'] ?? null,
            'minimum' => $validated['minimum'] ?? null,
            'C_DESCRIPTION' => $validated['C_DESCRIPTION'] ?? null,
            'dc0' => $validated['dc0'] ?? null,
            'C_DATE_ACHAT' => $validated['C_DATE_ACHAT'] ?? null,
            'C_DATE_PEREMPTION' => $validated['C_DATE_PEREMPTION'] ?? null,
            'C_LIEU_STOCKAGE' => $validated['C_LIEU_STOCKAGE'] ?? null,
            'S_ID' => $validated['S_ID'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
            'TC_ID' => $validated['TC_ID'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_consommable.edit', $item->id)
            ->with('success', 'Consommable updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Consommable::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.upd_consommable.index')
            ->with('success', 'Consommable deleted successfully');
    }
                
}
