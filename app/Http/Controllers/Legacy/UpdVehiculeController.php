<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Vehicule;
use Illuminate\Http\Request;

/**
 * Legacy migration source: upd_vehicule.php
 * Legacy pattern: edit
 * Legacy permission id: 42
 * This file stems from a legacy migration and requires functional verification.
 */
class UpdVehiculeController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.upd_vehicule.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Vehicule::findOrFail($id);

        return view('legacy_migrated.upd_vehicule.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'V_ID' => 'nullable|string|max:255',
            'V_IMMATRICULATION' => 'nullable|string|max:255',
            'V_COMMENT' => 'nullable|string|max:255',
            'VP_ID' => 'nullable|string|max:255',
            'V_KM' => 'nullable|string|max:255',
            'V_KM_REVISION' => 'nullable|string|max:255',
            'EQ_ID' => 'nullable|string|max:255',
            'V_MODELE' => 'nullable|string|max:255',
            'V_ANNEE' => 'nullable|string|max:255',
            'V_ASS_DATE' => 'nullable|string|max:255',
            'V_CT_DATE' => 'nullable|string|max:255',
            'V_REV_DATE' => 'nullable|string|max:255',
            'V_TITRE_DATE' => 'nullable|string|max:255',
            'V_INVENTAIRE' => 'nullable|string|max:255',
            'V_INDICATIF' => 'nullable|string|max:255',
            'from' => 'nullable|string|max:255',
            'P' => 'nullable|string|max:255',
            'V_EXTERNE' => 'nullable|string|max:255',
            'dc0' => 'nullable|string|max:255',
            'dc1' => 'nullable|string|max:255',
        ]);

        $item = Vehicule::create([
            'V_ID' => $validated['V_ID'] ?? null,
            'V_IMMATRICULATION' => $validated['V_IMMATRICULATION'] ?? null,
            'V_COMMENT' => $validated['V_COMMENT'] ?? null,
            'VP_ID' => $validated['VP_ID'] ?? null,
            'V_KM' => $validated['V_KM'] ?? null,
            'V_KM_REVISION' => $validated['V_KM_REVISION'] ?? null,
            'EQ_ID' => $validated['EQ_ID'] ?? null,
            'V_MODELE' => $validated['V_MODELE'] ?? null,
            'V_ANNEE' => $validated['V_ANNEE'] ?? null,
            'V_ASS_DATE' => $validated['V_ASS_DATE'] ?? null,
            'V_CT_DATE' => $validated['V_CT_DATE'] ?? null,
            'V_REV_DATE' => $validated['V_REV_DATE'] ?? null,
            'V_TITRE_DATE' => $validated['V_TITRE_DATE'] ?? null,
            'V_INVENTAIRE' => $validated['V_INVENTAIRE'] ?? null,
            'V_INDICATIF' => $validated['V_INDICATIF'] ?? null,
            'from' => $validated['from'] ?? null,
            'P' => $validated['P'] ?? null,
            'V_EXTERNE' => $validated['V_EXTERNE'] ?? null,
            'dc0' => $validated['dc0'] ?? null,
            'dc1' => $validated['dc1'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_vehicule.edit', $item->id)
            ->with('success', 'Vehicule created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Vehicule::findOrFail($id);

        $validated = $request->validate([
            'V_ID' => 'nullable|string|max:255',
            'V_IMMATRICULATION' => 'nullable|string|max:255',
            'V_COMMENT' => 'nullable|string|max:255',
            'VP_ID' => 'nullable|string|max:255',
            'V_KM' => 'nullable|string|max:255',
            'V_KM_REVISION' => 'nullable|string|max:255',
            'EQ_ID' => 'nullable|string|max:255',
            'V_MODELE' => 'nullable|string|max:255',
            'V_ANNEE' => 'nullable|string|max:255',
            'V_ASS_DATE' => 'nullable|string|max:255',
            'V_CT_DATE' => 'nullable|string|max:255',
            'V_REV_DATE' => 'nullable|string|max:255',
            'V_TITRE_DATE' => 'nullable|string|max:255',
            'V_INVENTAIRE' => 'nullable|string|max:255',
            'V_INDICATIF' => 'nullable|string|max:255',
            'from' => 'nullable|string|max:255',
            'P' => 'nullable|string|max:255',
            'V_EXTERNE' => 'nullable|string|max:255',
            'dc0' => 'nullable|string|max:255',
            'dc1' => 'nullable|string|max:255',
        ]);

        $item->update([
            'V_ID' => $validated['V_ID'] ?? null,
            'V_IMMATRICULATION' => $validated['V_IMMATRICULATION'] ?? null,
            'V_COMMENT' => $validated['V_COMMENT'] ?? null,
            'VP_ID' => $validated['VP_ID'] ?? null,
            'V_KM' => $validated['V_KM'] ?? null,
            'V_KM_REVISION' => $validated['V_KM_REVISION'] ?? null,
            'EQ_ID' => $validated['EQ_ID'] ?? null,
            'V_MODELE' => $validated['V_MODELE'] ?? null,
            'V_ANNEE' => $validated['V_ANNEE'] ?? null,
            'V_ASS_DATE' => $validated['V_ASS_DATE'] ?? null,
            'V_CT_DATE' => $validated['V_CT_DATE'] ?? null,
            'V_REV_DATE' => $validated['V_REV_DATE'] ?? null,
            'V_TITRE_DATE' => $validated['V_TITRE_DATE'] ?? null,
            'V_INVENTAIRE' => $validated['V_INVENTAIRE'] ?? null,
            'V_INDICATIF' => $validated['V_INDICATIF'] ?? null,
            'from' => $validated['from'] ?? null,
            'P' => $validated['P'] ?? null,
            'V_EXTERNE' => $validated['V_EXTERNE'] ?? null,
            'dc0' => $validated['dc0'] ?? null,
            'dc1' => $validated['dc1'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_vehicule.edit', $item->id)
            ->with('success', 'Vehicule updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Vehicule::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.upd_vehicule.index')
            ->with('success', 'Vehicule deleted successfully');
    }
                
}
