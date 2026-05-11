<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\NoteFrais;
use Illuminate\Http\Request;

/**
 * Legacy migration source: note_frais_save.php
 * Legacy pattern: save
 * Legacy permission id: 77
 * This file stems from a legacy migration and requires functional verification.
 */
class NoteFraisSaveController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.note_frais_save.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = NoteFrais::findOrFail($id);

        return view('legacy_migrated.note_frais_save.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reject_comment' => 'nullable|string|max:255',
            'Retour' => 'nullable|string|max:255',
            'annuler' => 'nullable|string|max:255',
            'nfid' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
            'person' => 'nullable|string|max:255',
            'sum' => 'nullable|string|max:255',
            'from' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'verified' => 'nullable|string|max:255',
            'don' => 'nullable|string|max:255',
            'justif_recus' => 'nullable|string|max:255',
            'frais_dep' => 'nullable|string|max:255',
            'national' => 'nullable|string|max:255',
            'departemental' => 'nullable|string|max:255',
            'motif' => 'nullable|string|max:255',
            'nfcode1' => 'nullable|string|max:255',
            'nfcode2' => 'nullable|string|max:255',
            'nfcode3' => 'nullable|string|max:255',
        ]);

        $item = NoteFrais::create([
            'reject_comment' => $validated['reject_comment'] ?? null,
            'Retour' => $validated['Retour'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
            'nfid' => $validated['nfid'] ?? null,
            'section' => $validated['section'] ?? null,
            'person' => $validated['person'] ?? null,
            'sum' => $validated['sum'] ?? null,
            'from' => $validated['from'] ?? null,
            'action' => $validated['action'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'verified' => $validated['verified'] ?? null,
            'don' => $validated['don'] ?? null,
            'justif_recus' => $validated['justif_recus'] ?? null,
            'frais_dep' => $validated['frais_dep'] ?? null,
            'national' => $validated['national'] ?? null,
            'departemental' => $validated['departemental'] ?? null,
            'motif' => $validated['motif'] ?? null,
            'nfcode1' => $validated['nfcode1'] ?? null,
            'nfcode2' => $validated['nfcode2'] ?? null,
            'nfcode3' => $validated['nfcode3'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.note_frais_save.edit', $item->id)
            ->with('success', 'NoteFrais created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = NoteFrais::findOrFail($id);

        $validated = $request->validate([
            'reject_comment' => 'nullable|string|max:255',
            'Retour' => 'nullable|string|max:255',
            'annuler' => 'nullable|string|max:255',
            'nfid' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
            'person' => 'nullable|string|max:255',
            'sum' => 'nullable|string|max:255',
            'from' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'verified' => 'nullable|string|max:255',
            'don' => 'nullable|string|max:255',
            'justif_recus' => 'nullable|string|max:255',
            'frais_dep' => 'nullable|string|max:255',
            'national' => 'nullable|string|max:255',
            'departemental' => 'nullable|string|max:255',
            'motif' => 'nullable|string|max:255',
            'nfcode1' => 'nullable|string|max:255',
            'nfcode2' => 'nullable|string|max:255',
            'nfcode3' => 'nullable|string|max:255',
        ]);

        $item->update([
            'reject_comment' => $validated['reject_comment'] ?? null,
            'Retour' => $validated['Retour'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
            'nfid' => $validated['nfid'] ?? null,
            'section' => $validated['section'] ?? null,
            'person' => $validated['person'] ?? null,
            'sum' => $validated['sum'] ?? null,
            'from' => $validated['from'] ?? null,
            'action' => $validated['action'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'verified' => $validated['verified'] ?? null,
            'don' => $validated['don'] ?? null,
            'justif_recus' => $validated['justif_recus'] ?? null,
            'frais_dep' => $validated['frais_dep'] ?? null,
            'national' => $validated['national'] ?? null,
            'departemental' => $validated['departemental'] ?? null,
            'motif' => $validated['motif'] ?? null,
            'nfcode1' => $validated['nfcode1'] ?? null,
            'nfcode2' => $validated['nfcode2'] ?? null,
            'nfcode3' => $validated['nfcode3'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.note_frais_save.edit', $item->id)
            ->with('success', 'NoteFrais updated successfully');
    }
                

    public function destroy($id)
    {
        $item = NoteFrais::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.note_frais_save.index')
            ->with('success', 'NoteFrais deleted successfully');
    }
                
}
