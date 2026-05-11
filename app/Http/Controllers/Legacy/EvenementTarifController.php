<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EvenementTarif;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_tarif.php
 * Legacy pattern: generic
 * Legacy permission id: 15
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementTarifController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementTarif::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.evenement_tarif.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.evenement_tarif.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EvenementTarif::findOrFail($id);

        return view('legacy_migrated.evenement_tarif.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = EvenementTarif::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.evenement_tarif.edit', $item->id)
            ->with('success', 'EvenementTarif created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EvenementTarif::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.evenement_tarif.edit', $item->id)
            ->with('success', 'EvenementTarif updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EvenementTarif::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenement_tarif.index')
            ->with('success', 'EvenementTarif deleted successfully');
    }
                
}
