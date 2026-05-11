<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\DetailFacture;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_detail_facture.php
 * Legacy pattern: save
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class SaveDetailFactureController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_detail_facture.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = DetailFacture::findOrFail($id);

        return view('legacy_migrated.save_detail_facture.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'evenement' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'btcopie' => 'nullable|string|max:255',
        ]);

        $item = DetailFacture::create([
            'evenement' => $validated['evenement'] ?? null,
            'type' => $validated['type'] ?? null,
            'btcopie' => $validated['btcopie'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_detail_facture.edit', $item->id)
            ->with('success', 'DetailFacture created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = DetailFacture::findOrFail($id);

        $validated = $request->validate([
            'evenement' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'btcopie' => 'nullable|string|max:255',
        ]);

        $item->update([
            'evenement' => $validated['evenement'] ?? null,
            'type' => $validated['type'] ?? null,
            'btcopie' => $validated['btcopie'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_detail_facture.edit', $item->id)
            ->with('success', 'DetailFacture updated successfully');
    }
                

    public function destroy($id)
    {
        $item = DetailFacture::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_detail_facture.index')
            ->with('success', 'DetailFacture deleted successfully');
    }
                
}
