<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Materiel;
use Illuminate\Http\Request;

/**
 * Legacy migration source: upd_materiel.php
 * Legacy pattern: edit
 * Legacy permission id: 42
 * This file stems from a legacy migration and requires functional verification.
 */
class UpdMaterielController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.upd_materiel.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Materiel::findOrFail($id);

        return view('legacy_migrated.upd_materiel.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'MA_ID' => 'nullable|string|max:255',
            'MA_NUMERO_SERIE' => 'nullable|string|max:255',
            'MA_COMMENT' => 'nullable|string|max:255',
            'VP_ID' => 'nullable|string|max:255',
            'MA_MODELE' => 'nullable|string|max:255',
            'MA_ANNEE' => 'nullable|string|max:255',
            'MA_REV_DATE' => 'nullable|string|max:255',
            'TM_USAGE' => 'nullable|string|max:255',
            'TV_ID' => 'nullable|string|max:255',
            'numlot' => 'nullable|string|max:255',
            'from' => 'nullable|string|max:255',
            'quantity' => 'nullable|string|max:255',
            'MA_INVENTAIRE' => 'nullable|string|max:255',
            'MA_LIEU_STOCKAGE' => 'nullable|string|max:255',
            'dc0' => 'nullable|string|max:255',
            'dc1' => 'nullable|string|max:255',
            'MA_EXTERNE' => 'nullable|string|max:255',
            'TM_CODE' => 'nullable|string|max:255',
            'groupe' => 'nullable|string|max:255',
            'MA_INVENTAIRE2' => 'nullable|string|max:255',
        ]);

        $item = Materiel::create([
            'MA_ID' => $validated['MA_ID'] ?? null,
            'MA_NUMERO_SERIE' => $validated['MA_NUMERO_SERIE'] ?? null,
            'MA_COMMENT' => $validated['MA_COMMENT'] ?? null,
            'VP_ID' => $validated['VP_ID'] ?? null,
            'MA_MODELE' => $validated['MA_MODELE'] ?? null,
            'MA_ANNEE' => $validated['MA_ANNEE'] ?? null,
            'MA_REV_DATE' => $validated['MA_REV_DATE'] ?? null,
            'TM_USAGE' => $validated['TM_USAGE'] ?? null,
            'TV_ID' => $validated['TV_ID'] ?? null,
            'numlot' => $validated['numlot'] ?? null,
            'from' => $validated['from'] ?? null,
            'quantity' => $validated['quantity'] ?? null,
            'MA_INVENTAIRE' => $validated['MA_INVENTAIRE'] ?? null,
            'MA_LIEU_STOCKAGE' => $validated['MA_LIEU_STOCKAGE'] ?? null,
            'dc0' => $validated['dc0'] ?? null,
            'dc1' => $validated['dc1'] ?? null,
            'MA_EXTERNE' => $validated['MA_EXTERNE'] ?? null,
            'TM_CODE' => $validated['TM_CODE'] ?? null,
            'groupe' => $validated['groupe'] ?? null,
            'MA_INVENTAIRE2' => $validated['MA_INVENTAIRE2'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_materiel.edit', $item->id)
            ->with('success', 'Materiel created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Materiel::findOrFail($id);

        $validated = $request->validate([
            'MA_ID' => 'nullable|string|max:255',
            'MA_NUMERO_SERIE' => 'nullable|string|max:255',
            'MA_COMMENT' => 'nullable|string|max:255',
            'VP_ID' => 'nullable|string|max:255',
            'MA_MODELE' => 'nullable|string|max:255',
            'MA_ANNEE' => 'nullable|string|max:255',
            'MA_REV_DATE' => 'nullable|string|max:255',
            'TM_USAGE' => 'nullable|string|max:255',
            'TV_ID' => 'nullable|string|max:255',
            'numlot' => 'nullable|string|max:255',
            'from' => 'nullable|string|max:255',
            'quantity' => 'nullable|string|max:255',
            'MA_INVENTAIRE' => 'nullable|string|max:255',
            'MA_LIEU_STOCKAGE' => 'nullable|string|max:255',
            'dc0' => 'nullable|string|max:255',
            'dc1' => 'nullable|string|max:255',
            'MA_EXTERNE' => 'nullable|string|max:255',
            'TM_CODE' => 'nullable|string|max:255',
            'groupe' => 'nullable|string|max:255',
            'MA_INVENTAIRE2' => 'nullable|string|max:255',
        ]);

        $item->update([
            'MA_ID' => $validated['MA_ID'] ?? null,
            'MA_NUMERO_SERIE' => $validated['MA_NUMERO_SERIE'] ?? null,
            'MA_COMMENT' => $validated['MA_COMMENT'] ?? null,
            'VP_ID' => $validated['VP_ID'] ?? null,
            'MA_MODELE' => $validated['MA_MODELE'] ?? null,
            'MA_ANNEE' => $validated['MA_ANNEE'] ?? null,
            'MA_REV_DATE' => $validated['MA_REV_DATE'] ?? null,
            'TM_USAGE' => $validated['TM_USAGE'] ?? null,
            'TV_ID' => $validated['TV_ID'] ?? null,
            'numlot' => $validated['numlot'] ?? null,
            'from' => $validated['from'] ?? null,
            'quantity' => $validated['quantity'] ?? null,
            'MA_INVENTAIRE' => $validated['MA_INVENTAIRE'] ?? null,
            'MA_LIEU_STOCKAGE' => $validated['MA_LIEU_STOCKAGE'] ?? null,
            'dc0' => $validated['dc0'] ?? null,
            'dc1' => $validated['dc1'] ?? null,
            'MA_EXTERNE' => $validated['MA_EXTERNE'] ?? null,
            'TM_CODE' => $validated['TM_CODE'] ?? null,
            'groupe' => $validated['groupe'] ?? null,
            'MA_INVENTAIRE2' => $validated['MA_INVENTAIRE2'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_materiel.edit', $item->id)
            ->with('success', 'Materiel updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Materiel::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.upd_materiel.index')
            ->with('success', 'Materiel deleted successfully');
    }
                
}
