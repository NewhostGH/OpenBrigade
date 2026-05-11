<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Cotisations;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_cotisations.php
 * Legacy pattern: save
 * Legacy permission id: 53
 * This file stems from a legacy migration and requires functional verification.
 */
class SaveCotisationsController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_cotisations.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Cotisations::findOrFail($id);

        return view('legacy_migrated.save_cotisations.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'filter' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
            'periode' => 'nullable|string|max:255',
            'type_paiement' => 'nullable|string|max:255',
            'people' => 'nullable|string|max:255',
        ]);

        $item = Cotisations::create([
            'filter' => $validated['filter'] ?? null,
            'year' => $validated['year'] ?? null,
            'periode' => $validated['periode'] ?? null,
            'type_paiement' => $validated['type_paiement'] ?? null,
            'people' => $validated['people'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_cotisations.edit', $item->id)
            ->with('success', 'Cotisations created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Cotisations::findOrFail($id);

        $validated = $request->validate([
            'filter' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
            'periode' => 'nullable|string|max:255',
            'type_paiement' => 'nullable|string|max:255',
            'people' => 'nullable|string|max:255',
        ]);

        $item->update([
            'filter' => $validated['filter'] ?? null,
            'year' => $validated['year'] ?? null,
            'periode' => $validated['periode'] ?? null,
            'type_paiement' => $validated['type_paiement'] ?? null,
            'people' => $validated['people'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_cotisations.edit', $item->id)
            ->with('success', 'Cotisations updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Cotisations::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_cotisations.index')
            ->with('success', 'Cotisations deleted successfully');
    }
                
}
