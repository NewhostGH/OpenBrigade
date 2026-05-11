<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Materiel;
use Illuminate\Http\Request;

/**
 * Legacy migration source: ins_materiel.php
 * Legacy pattern: create
 * Legacy permission id: 70
 * This file stems from a legacy migration and requires functional verification.
 */
class InsMaterielController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.ins_materiel.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Materiel::findOrFail($id);

        return view('legacy_migrated.ins_materiel.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'TM_ID' => 'nullable|string|max:255',
            'MA_NUMERO_SERIE' => 'nullable|string|max:255',
            'MA_COMMENT' => 'nullable|string|max:255',
            'VP_ID' => 'nullable|string|max:255',
            'MA_ANNEE' => 'nullable|string|max:255',
            'MA_INVENTAIRE' => 'nullable|string|max:255',
            'MA_REV_DATE' => 'nullable|string|max:255',
            'groupe' => 'nullable|string|max:255',
            'MA_ID' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'from' => 'nullable|string|max:255',
            'MA_MODELE' => 'nullable|string|max:255',
            'quantity' => 'nullable|string|max:255',
            'MA_LIEU_STOCKAGE' => 'nullable|string|max:255',
            'dc0' => 'nullable|string|max:255',
            'dc1' => 'nullable|string|max:255',
            'MA_EXTERNE' => 'nullable|string|max:255',
            'annuler' => 'nullable|string|max:255',
            'TM_USAGE' => 'nullable|string|max:255',
            'affected_to' => 'nullable|string|max:255',
        ]);

        $item = Materiel::create([
            'TM_ID' => $validated['TM_ID'] ?? null,
            'MA_NUMERO_SERIE' => $validated['MA_NUMERO_SERIE'] ?? null,
            'MA_COMMENT' => $validated['MA_COMMENT'] ?? null,
            'VP_ID' => $validated['VP_ID'] ?? null,
            'MA_ANNEE' => $validated['MA_ANNEE'] ?? null,
            'MA_INVENTAIRE' => $validated['MA_INVENTAIRE'] ?? null,
            'MA_REV_DATE' => $validated['MA_REV_DATE'] ?? null,
            'groupe' => $validated['groupe'] ?? null,
            'MA_ID' => $validated['MA_ID'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'from' => $validated['from'] ?? null,
            'MA_MODELE' => $validated['MA_MODELE'] ?? null,
            'quantity' => $validated['quantity'] ?? null,
            'MA_LIEU_STOCKAGE' => $validated['MA_LIEU_STOCKAGE'] ?? null,
            'dc0' => $validated['dc0'] ?? null,
            'dc1' => $validated['dc1'] ?? null,
            'MA_EXTERNE' => $validated['MA_EXTERNE'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
            'TM_USAGE' => $validated['TM_USAGE'] ?? null,
            'affected_to' => $validated['affected_to'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.ins_materiel.edit', $item->id)
            ->with('success', 'Materiel created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Materiel::findOrFail($id);

        $validated = $request->validate([
            'TM_ID' => 'nullable|string|max:255',
            'MA_NUMERO_SERIE' => 'nullable|string|max:255',
            'MA_COMMENT' => 'nullable|string|max:255',
            'VP_ID' => 'nullable|string|max:255',
            'MA_ANNEE' => 'nullable|string|max:255',
            'MA_INVENTAIRE' => 'nullable|string|max:255',
            'MA_REV_DATE' => 'nullable|string|max:255',
            'groupe' => 'nullable|string|max:255',
            'MA_ID' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'from' => 'nullable|string|max:255',
            'MA_MODELE' => 'nullable|string|max:255',
            'quantity' => 'nullable|string|max:255',
            'MA_LIEU_STOCKAGE' => 'nullable|string|max:255',
            'dc0' => 'nullable|string|max:255',
            'dc1' => 'nullable|string|max:255',
            'MA_EXTERNE' => 'nullable|string|max:255',
            'annuler' => 'nullable|string|max:255',
            'TM_USAGE' => 'nullable|string|max:255',
            'affected_to' => 'nullable|string|max:255',
        ]);

        $item->update([
            'TM_ID' => $validated['TM_ID'] ?? null,
            'MA_NUMERO_SERIE' => $validated['MA_NUMERO_SERIE'] ?? null,
            'MA_COMMENT' => $validated['MA_COMMENT'] ?? null,
            'VP_ID' => $validated['VP_ID'] ?? null,
            'MA_ANNEE' => $validated['MA_ANNEE'] ?? null,
            'MA_INVENTAIRE' => $validated['MA_INVENTAIRE'] ?? null,
            'MA_REV_DATE' => $validated['MA_REV_DATE'] ?? null,
            'groupe' => $validated['groupe'] ?? null,
            'MA_ID' => $validated['MA_ID'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'from' => $validated['from'] ?? null,
            'MA_MODELE' => $validated['MA_MODELE'] ?? null,
            'quantity' => $validated['quantity'] ?? null,
            'MA_LIEU_STOCKAGE' => $validated['MA_LIEU_STOCKAGE'] ?? null,
            'dc0' => $validated['dc0'] ?? null,
            'dc1' => $validated['dc1'] ?? null,
            'MA_EXTERNE' => $validated['MA_EXTERNE'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
            'TM_USAGE' => $validated['TM_USAGE'] ?? null,
            'affected_to' => $validated['affected_to'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.ins_materiel.edit', $item->id)
            ->with('success', 'Materiel updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Materiel::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.ins_materiel.index')
            ->with('success', 'Materiel deleted successfully');
    }
                
}
