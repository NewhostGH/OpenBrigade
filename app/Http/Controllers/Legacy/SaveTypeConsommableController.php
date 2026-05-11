<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\TypeConsommable;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_type_consommable.php
 * Legacy pattern: save
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class SaveTypeConsommableController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_type_consommable.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = TypeConsommable::findOrFail($id);

        return view('legacy_migrated.save_type_consommable.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'TC_ID' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'CC_CODE' => 'nullable|string|max:255',
            'TC_DESCRIPTION' => 'nullable|string|max:255',
            'TCO_CODE' => 'nullable|string|max:255',
            'TUM_CODE' => 'nullable|string|max:255',
            'TC_QUANTITE_PAR_UNITE' => 'nullable|string|max:255',
            'TC_PEREMPTION' => 'nullable|string|max:255',
            'from' => 'nullable|string|max:255',
        ]);

        $item = TypeConsommable::create([
            'TC_ID' => $validated['TC_ID'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'CC_CODE' => $validated['CC_CODE'] ?? null,
            'TC_DESCRIPTION' => $validated['TC_DESCRIPTION'] ?? null,
            'TCO_CODE' => $validated['TCO_CODE'] ?? null,
            'TUM_CODE' => $validated['TUM_CODE'] ?? null,
            'TC_QUANTITE_PAR_UNITE' => $validated['TC_QUANTITE_PAR_UNITE'] ?? null,
            'TC_PEREMPTION' => $validated['TC_PEREMPTION'] ?? null,
            'from' => $validated['from'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_type_consommable.edit', $item->id)
            ->with('success', 'TypeConsommable created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = TypeConsommable::findOrFail($id);

        $validated = $request->validate([
            'TC_ID' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'CC_CODE' => 'nullable|string|max:255',
            'TC_DESCRIPTION' => 'nullable|string|max:255',
            'TCO_CODE' => 'nullable|string|max:255',
            'TUM_CODE' => 'nullable|string|max:255',
            'TC_QUANTITE_PAR_UNITE' => 'nullable|string|max:255',
            'TC_PEREMPTION' => 'nullable|string|max:255',
            'from' => 'nullable|string|max:255',
        ]);

        $item->update([
            'TC_ID' => $validated['TC_ID'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'CC_CODE' => $validated['CC_CODE'] ?? null,
            'TC_DESCRIPTION' => $validated['TC_DESCRIPTION'] ?? null,
            'TCO_CODE' => $validated['TCO_CODE'] ?? null,
            'TUM_CODE' => $validated['TUM_CODE'] ?? null,
            'TC_QUANTITE_PAR_UNITE' => $validated['TC_QUANTITE_PAR_UNITE'] ?? null,
            'TC_PEREMPTION' => $validated['TC_PEREMPTION'] ?? null,
            'from' => $validated['from'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_type_consommable.edit', $item->id)
            ->with('success', 'TypeConsommable updated successfully');
    }
                

    public function destroy($id)
    {
        $item = TypeConsommable::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_type_consommable.index')
            ->with('success', 'TypeConsommable deleted successfully');
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
