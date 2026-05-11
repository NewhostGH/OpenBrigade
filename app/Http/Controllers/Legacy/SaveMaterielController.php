<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Materiel;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_materiel.php
 * Legacy pattern: save
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class SaveMaterielController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_materiel.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Materiel::findOrFail($id);

        return view('legacy_migrated.save_materiel.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'section' => 'nullable|string|max:255',
            'materiel' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'security' => 'nullable|string|max:255',
            'MA_NUMERO_SERIE' => 'nullable|string|max:255',
            'MA_COMMENT' => 'nullable|string|max:255',
            'MA_INVENTAIRE' => 'nullable|string|max:255',
            'MA_INVENTAIRE2' => 'nullable|string|max:255',
            'MA_MODELE' => 'nullable|string|max:255',
            'MA_LIEU_STOCKAGE' => 'nullable|string|max:255',
            'TM_ID' => 'nullable|string|max:255',
            'TM_USAGE' => 'nullable|string|max:255',
            'MA_ID' => 'nullable|string|max:255',
            'TV_ID' => 'nullable|string|max:255',
            'MA_ANNEE' => 'nullable|string|max:255',
            'quantity' => 'nullable|string|max:255',
            'VP_ID' => 'nullable|string|max:255',
            'groupe' => 'nullable|string|max:255',
            'dc1' => 'nullable|string|max:255',
            'MA_EXTERNE' => 'nullable|string|max:255',
        ]);

        $item = Materiel::create([
            'section' => $validated['section'] ?? null,
            'materiel' => $validated['materiel'] ?? null,
            'type' => $validated['type'] ?? null,
            'security' => $validated['security'] ?? null,
            'MA_NUMERO_SERIE' => $validated['MA_NUMERO_SERIE'] ?? null,
            'MA_COMMENT' => $validated['MA_COMMENT'] ?? null,
            'MA_INVENTAIRE' => $validated['MA_INVENTAIRE'] ?? null,
            'MA_INVENTAIRE2' => $validated['MA_INVENTAIRE2'] ?? null,
            'MA_MODELE' => $validated['MA_MODELE'] ?? null,
            'MA_LIEU_STOCKAGE' => $validated['MA_LIEU_STOCKAGE'] ?? null,
            'TM_ID' => $validated['TM_ID'] ?? null,
            'TM_USAGE' => $validated['TM_USAGE'] ?? null,
            'MA_ID' => $validated['MA_ID'] ?? null,
            'TV_ID' => $validated['TV_ID'] ?? null,
            'MA_ANNEE' => $validated['MA_ANNEE'] ?? null,
            'quantity' => $validated['quantity'] ?? null,
            'VP_ID' => $validated['VP_ID'] ?? null,
            'groupe' => $validated['groupe'] ?? null,
            'dc1' => $validated['dc1'] ?? null,
            'MA_EXTERNE' => $validated['MA_EXTERNE'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_materiel.edit', $item->id)
            ->with('success', 'Materiel created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Materiel::findOrFail($id);

        $validated = $request->validate([
            'section' => 'nullable|string|max:255',
            'materiel' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'security' => 'nullable|string|max:255',
            'MA_NUMERO_SERIE' => 'nullable|string|max:255',
            'MA_COMMENT' => 'nullable|string|max:255',
            'MA_INVENTAIRE' => 'nullable|string|max:255',
            'MA_INVENTAIRE2' => 'nullable|string|max:255',
            'MA_MODELE' => 'nullable|string|max:255',
            'MA_LIEU_STOCKAGE' => 'nullable|string|max:255',
            'TM_ID' => 'nullable|string|max:255',
            'TM_USAGE' => 'nullable|string|max:255',
            'MA_ID' => 'nullable|string|max:255',
            'TV_ID' => 'nullable|string|max:255',
            'MA_ANNEE' => 'nullable|string|max:255',
            'quantity' => 'nullable|string|max:255',
            'VP_ID' => 'nullable|string|max:255',
            'groupe' => 'nullable|string|max:255',
            'dc1' => 'nullable|string|max:255',
            'MA_EXTERNE' => 'nullable|string|max:255',
        ]);

        $item->update([
            'section' => $validated['section'] ?? null,
            'materiel' => $validated['materiel'] ?? null,
            'type' => $validated['type'] ?? null,
            'security' => $validated['security'] ?? null,
            'MA_NUMERO_SERIE' => $validated['MA_NUMERO_SERIE'] ?? null,
            'MA_COMMENT' => $validated['MA_COMMENT'] ?? null,
            'MA_INVENTAIRE' => $validated['MA_INVENTAIRE'] ?? null,
            'MA_INVENTAIRE2' => $validated['MA_INVENTAIRE2'] ?? null,
            'MA_MODELE' => $validated['MA_MODELE'] ?? null,
            'MA_LIEU_STOCKAGE' => $validated['MA_LIEU_STOCKAGE'] ?? null,
            'TM_ID' => $validated['TM_ID'] ?? null,
            'TM_USAGE' => $validated['TM_USAGE'] ?? null,
            'MA_ID' => $validated['MA_ID'] ?? null,
            'TV_ID' => $validated['TV_ID'] ?? null,
            'MA_ANNEE' => $validated['MA_ANNEE'] ?? null,
            'quantity' => $validated['quantity'] ?? null,
            'VP_ID' => $validated['VP_ID'] ?? null,
            'groupe' => $validated['groupe'] ?? null,
            'dc1' => $validated['dc1'] ?? null,
            'MA_EXTERNE' => $validated['MA_EXTERNE'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_materiel.edit', $item->id)
            ->with('success', 'Materiel updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Materiel::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_materiel.index')
            ->with('success', 'Materiel deleted successfully');
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
