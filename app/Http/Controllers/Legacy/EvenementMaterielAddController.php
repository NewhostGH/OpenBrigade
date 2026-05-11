<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EvenementMaterielAdd;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_materiel_add.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementMaterielAddController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementMaterielAdd::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('e_open_to_ext', 'like', '%' . $term . '%');
                $query->orWhere('s_id', 'like', '%' . $term . '%');
                $query->orWhere('ma_nb', 'like', '%' . $term . '%');
                $query->orWhere('ma_id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.evenement_materiel_add.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.evenement_materiel_add.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EvenementMaterielAdd::findOrFail($id);

        return view('legacy_migrated.evenement_materiel_add.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'MA_ID' => 'nullable|string|max:255',
            'nb' => 'nullable|string|max:255',
            'EC' => 'nullable|string|max:255',
        ]);

        $item = EvenementMaterielAdd::create([
            'from' => $validated['from'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'action' => $validated['action'] ?? null,
            'MA_ID' => $validated['MA_ID'] ?? null,
            'nb' => $validated['nb'] ?? null,
            'EC' => $validated['EC'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_materiel_add.edit', $item->id)
            ->with('success', 'EvenementMaterielAdd created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EvenementMaterielAdd::findOrFail($id);

        $validated = $request->validate([
            'from' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'MA_ID' => 'nullable|string|max:255',
            'nb' => 'nullable|string|max:255',
            'EC' => 'nullable|string|max:255',
        ]);

        $item->update([
            'from' => $validated['from'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'action' => $validated['action'] ?? null,
            'MA_ID' => $validated['MA_ID'] ?? null,
            'nb' => $validated['nb'] ?? null,
            'EC' => $validated['EC'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_materiel_add.edit', $item->id)
            ->with('success', 'EvenementMaterielAdd updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EvenementMaterielAdd::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenement_materiel_add.index')
            ->with('success', 'EvenementMaterielAdd deleted successfully');
    }
                
}
