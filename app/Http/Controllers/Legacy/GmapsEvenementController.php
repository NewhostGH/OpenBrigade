<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\GmapsEvenement;
use Illuminate\Http\Request;

/**
 * Legacy migration source: gmaps_evenement.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class GmapsEvenementController extends Controller
{
    public function index(Request $request)
    {
        $query = GmapsEvenement::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('e_code', 'like', '%' . $term . '%');
                $query->orWhere('te_code', 'like', '%' . $term . '%');
                $query->orWhere('te_libelle', 'like', '%' . $term . '%');
                $query->orWhere('e_lieu', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.gmaps_evenement.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.gmaps_evenement.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = GmapsEvenement::findOrFail($id);

        return view('legacy_migrated.gmaps_evenement.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'display' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'type_evenement' => 'nullable|string|max:255',
        ]);

        $item = GmapsEvenement::create([
            'sub' => $validated['sub'] ?? null,
            'display' => $validated['display'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'type_evenement' => $validated['type_evenement'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.gmaps_evenement.edit', $item->id)
            ->with('success', 'GmapsEvenement created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = GmapsEvenement::findOrFail($id);

        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'display' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'type_evenement' => 'nullable|string|max:255',
        ]);

        $item->update([
            'sub' => $validated['sub'] ?? null,
            'display' => $validated['display'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'type_evenement' => $validated['type_evenement'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.gmaps_evenement.edit', $item->id)
            ->with('success', 'GmapsEvenement updated successfully');
    }
                

    public function destroy($id)
    {
        $item = GmapsEvenement::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.gmaps_evenement.index')
            ->with('success', 'GmapsEvenement deleted successfully');
    }
                
}
