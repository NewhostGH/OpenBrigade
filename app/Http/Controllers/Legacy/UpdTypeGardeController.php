<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\TypeGarde;
use Illuminate\Http\Request;

/**
 * Legacy migration source: upd_type_garde.php
 * Legacy pattern: edit
 * Legacy permission id: 5
 * This file stems from a legacy migration and requires functional verification.
 */
class UpdTypeGardeController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.upd_type_garde.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = TypeGarde::findOrFail($id);

        return view('legacy_migrated.upd_type_garde.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'EQ_ID' => 'nullable|string|max:255',
            'groupe' => 'nullable|string|max:255',
            'EQ_NOM' => 'nullable|string|max:255',
            'EQ_LIEU' => 'nullable|string|max:255',
            'EQ_ADDRESS' => 'nullable|string|max:255',
            'date1' => 'nullable|string|max:255',
            'date2' => 'nullable|string|max:255',
            'EQ_JOUR' => 'nullable|string|max:255',
            'EQ_PERSONNEL1' => 'nullable|string|max:255',
            'EQ_NUIT' => 'nullable|string|max:255',
            'EQ_PERSONNEL2' => 'nullable|string|max:255',
            'EQ_VEHICULES' => 'nullable|string|max:255',
            'EQ_SPP' => 'nullable|string|max:255',
            'EQ_DEFAULT' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'annuler' => 'nullable|string|max:255',
            'EQ_REGIME_TRAVAIL' => 'nullable|string|max:255',
            'debut1' => 'nullable|string|max:255',
            'fin1' => 'nullable|string|max:255',
            'duree1' => 'nullable|string|max:255',
        ]);

        $item = TypeGarde::create([
            'EQ_ID' => $validated['EQ_ID'] ?? null,
            'groupe' => $validated['groupe'] ?? null,
            'EQ_NOM' => $validated['EQ_NOM'] ?? null,
            'EQ_LIEU' => $validated['EQ_LIEU'] ?? null,
            'EQ_ADDRESS' => $validated['EQ_ADDRESS'] ?? null,
            'date1' => $validated['date1'] ?? null,
            'date2' => $validated['date2'] ?? null,
            'EQ_JOUR' => $validated['EQ_JOUR'] ?? null,
            'EQ_PERSONNEL1' => $validated['EQ_PERSONNEL1'] ?? null,
            'EQ_NUIT' => $validated['EQ_NUIT'] ?? null,
            'EQ_PERSONNEL2' => $validated['EQ_PERSONNEL2'] ?? null,
            'EQ_VEHICULES' => $validated['EQ_VEHICULES'] ?? null,
            'EQ_SPP' => $validated['EQ_SPP'] ?? null,
            'EQ_DEFAULT' => $validated['EQ_DEFAULT'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
            'EQ_REGIME_TRAVAIL' => $validated['EQ_REGIME_TRAVAIL'] ?? null,
            'debut1' => $validated['debut1'] ?? null,
            'fin1' => $validated['fin1'] ?? null,
            'duree1' => $validated['duree1'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_type_garde.edit', $item->id)
            ->with('success', 'TypeGarde created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = TypeGarde::findOrFail($id);

        $validated = $request->validate([
            'EQ_ID' => 'nullable|string|max:255',
            'groupe' => 'nullable|string|max:255',
            'EQ_NOM' => 'nullable|string|max:255',
            'EQ_LIEU' => 'nullable|string|max:255',
            'EQ_ADDRESS' => 'nullable|string|max:255',
            'date1' => 'nullable|string|max:255',
            'date2' => 'nullable|string|max:255',
            'EQ_JOUR' => 'nullable|string|max:255',
            'EQ_PERSONNEL1' => 'nullable|string|max:255',
            'EQ_NUIT' => 'nullable|string|max:255',
            'EQ_PERSONNEL2' => 'nullable|string|max:255',
            'EQ_VEHICULES' => 'nullable|string|max:255',
            'EQ_SPP' => 'nullable|string|max:255',
            'EQ_DEFAULT' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'annuler' => 'nullable|string|max:255',
            'EQ_REGIME_TRAVAIL' => 'nullable|string|max:255',
            'debut1' => 'nullable|string|max:255',
            'fin1' => 'nullable|string|max:255',
            'duree1' => 'nullable|string|max:255',
        ]);

        $item->update([
            'EQ_ID' => $validated['EQ_ID'] ?? null,
            'groupe' => $validated['groupe'] ?? null,
            'EQ_NOM' => $validated['EQ_NOM'] ?? null,
            'EQ_LIEU' => $validated['EQ_LIEU'] ?? null,
            'EQ_ADDRESS' => $validated['EQ_ADDRESS'] ?? null,
            'date1' => $validated['date1'] ?? null,
            'date2' => $validated['date2'] ?? null,
            'EQ_JOUR' => $validated['EQ_JOUR'] ?? null,
            'EQ_PERSONNEL1' => $validated['EQ_PERSONNEL1'] ?? null,
            'EQ_NUIT' => $validated['EQ_NUIT'] ?? null,
            'EQ_PERSONNEL2' => $validated['EQ_PERSONNEL2'] ?? null,
            'EQ_VEHICULES' => $validated['EQ_VEHICULES'] ?? null,
            'EQ_SPP' => $validated['EQ_SPP'] ?? null,
            'EQ_DEFAULT' => $validated['EQ_DEFAULT'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
            'EQ_REGIME_TRAVAIL' => $validated['EQ_REGIME_TRAVAIL'] ?? null,
            'debut1' => $validated['debut1'] ?? null,
            'fin1' => $validated['fin1'] ?? null,
            'duree1' => $validated['duree1'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_type_garde.edit', $item->id)
            ->with('success', 'TypeGarde updated successfully');
    }
                

    public function destroy($id)
    {
        $item = TypeGarde::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.upd_type_garde.index')
            ->with('success', 'TypeGarde deleted successfully');
    }
                

    public function applyOperation(Request $request)
    {
        $operation = (string) $request->input('operation');

        if ($operation === 'insert') {
            return response()->json(['status' => 'ok', 'operation' => 'insert']);
        }

        return response()->json(['status' => 'ignored', 'operation' => $operation]);
    }
}
