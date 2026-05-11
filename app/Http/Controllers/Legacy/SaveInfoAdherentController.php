<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\InfoAdherent;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_info_adherent.php
 * Legacy pattern: save
 * Legacy permission id: 53
 * This file stems from a legacy migration and requires functional verification.
 */
class SaveInfoAdherentController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_info_adherent.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = InfoAdherent::findOrFail($id);

        return view('legacy_migrated.save_info_adherent.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'P_ID' => 'nullable|string|max:255',
            'type_paiement' => 'nullable|string|max:255',
            'montant_regul' => 'nullable|string|max:255',
            'bic' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:255',
        ]);

        $item = InfoAdherent::create([
            'P_ID' => $validated['P_ID'] ?? null,
            'type_paiement' => $validated['type_paiement'] ?? null,
            'montant_regul' => $validated['montant_regul'] ?? null,
            'bic' => $validated['bic'] ?? null,
            'iban' => $validated['iban'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_info_adherent.edit', $item->id)
            ->with('success', 'InfoAdherent created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = InfoAdherent::findOrFail($id);

        $validated = $request->validate([
            'P_ID' => 'nullable|string|max:255',
            'type_paiement' => 'nullable|string|max:255',
            'montant_regul' => 'nullable|string|max:255',
            'bic' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:255',
        ]);

        $item->update([
            'P_ID' => $validated['P_ID'] ?? null,
            'type_paiement' => $validated['type_paiement'] ?? null,
            'montant_regul' => $validated['montant_regul'] ?? null,
            'bic' => $validated['bic'] ?? null,
            'iban' => $validated['iban'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_info_adherent.edit', $item->id)
            ->with('success', 'InfoAdherent updated successfully');
    }
                

    public function destroy($id)
    {
        $item = InfoAdherent::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_info_adherent.index')
            ->with('success', 'InfoAdherent deleted successfully');
    }
                
}
