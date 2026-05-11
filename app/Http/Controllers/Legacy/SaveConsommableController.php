<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Consommable;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_consommable.php
 * Legacy pattern: save
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class SaveConsommableController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_consommable.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Consommable::findOrFail($id);

        return view('legacy_migrated.save_consommable.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'C_ID' => 'nullable|string|max:255',
            'TC_ID' => 'nullable|string|max:255',
            'S_ID' => 'nullable|string|max:255',
            'quantity' => 'nullable|string|max:255',
            'minimum' => 'nullable|string|max:255',
            'C_DATE_ACHAT' => 'nullable|string|max:255',
            'C_DATE_PEREMPTION' => 'nullable|string|max:255',
            'C_DESCRIPTION' => 'nullable|string|max:255',
            'C_LIEU_STOCKAGE' => 'nullable|string|max:255',
            'numlot' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
        ]);

        $item = Consommable::create([
            'C_ID' => $validated['C_ID'] ?? null,
            'TC_ID' => $validated['TC_ID'] ?? null,
            'S_ID' => $validated['S_ID'] ?? null,
            'quantity' => $validated['quantity'] ?? null,
            'minimum' => $validated['minimum'] ?? null,
            'C_DATE_ACHAT' => $validated['C_DATE_ACHAT'] ?? null,
            'C_DATE_PEREMPTION' => $validated['C_DATE_PEREMPTION'] ?? null,
            'C_DESCRIPTION' => $validated['C_DESCRIPTION'] ?? null,
            'C_LIEU_STOCKAGE' => $validated['C_LIEU_STOCKAGE'] ?? null,
            'numlot' => $validated['numlot'] ?? null,
            'operation' => $validated['operation'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_consommable.edit', $item->id)
            ->with('success', 'Consommable created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Consommable::findOrFail($id);

        $validated = $request->validate([
            'C_ID' => 'nullable|string|max:255',
            'TC_ID' => 'nullable|string|max:255',
            'S_ID' => 'nullable|string|max:255',
            'quantity' => 'nullable|string|max:255',
            'minimum' => 'nullable|string|max:255',
            'C_DATE_ACHAT' => 'nullable|string|max:255',
            'C_DATE_PEREMPTION' => 'nullable|string|max:255',
            'C_DESCRIPTION' => 'nullable|string|max:255',
            'C_LIEU_STOCKAGE' => 'nullable|string|max:255',
            'numlot' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
        ]);

        $item->update([
            'C_ID' => $validated['C_ID'] ?? null,
            'TC_ID' => $validated['TC_ID'] ?? null,
            'S_ID' => $validated['S_ID'] ?? null,
            'quantity' => $validated['quantity'] ?? null,
            'minimum' => $validated['minimum'] ?? null,
            'C_DATE_ACHAT' => $validated['C_DATE_ACHAT'] ?? null,
            'C_DATE_PEREMPTION' => $validated['C_DATE_PEREMPTION'] ?? null,
            'C_DESCRIPTION' => $validated['C_DESCRIPTION'] ?? null,
            'C_LIEU_STOCKAGE' => $validated['C_LIEU_STOCKAGE'] ?? null,
            'numlot' => $validated['numlot'] ?? null,
            'operation' => $validated['operation'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_consommable.edit', $item->id)
            ->with('success', 'Consommable updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Consommable::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_consommable.index')
            ->with('success', 'Consommable deleted successfully');
    }
                

    public function applyOperation(Request $request)
    {
        $operation = (string) $request->input('operation');

        if ($operation === 'delete') {
            return response()->json(['status' => 'ok', 'operation' => 'delete']);
        }

        if ($operation === 'insert') {
            return response()->json(['status' => 'ok', 'operation' => 'insert']);
        }

        if ($operation === 'update') {
            return response()->json(['status' => 'ok', 'operation' => 'update']);
        }

        return response()->json(['status' => 'ignored', 'operation' => $operation]);
    }
}
