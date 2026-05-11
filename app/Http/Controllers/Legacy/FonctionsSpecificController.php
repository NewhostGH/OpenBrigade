<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\FonctionsSpecific;
use Illuminate\Http\Request;

/**
 * Legacy migration source: fonctions_specific.php
 * Legacy pattern: list
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class FonctionsSpecificController extends Controller
{
    public function index(Request $request)
    {
        $query = FonctionsSpecific::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_id', 'like', '%' . $term . '%');
                $query->orWhere('count1', 'like', '%' . $term . '%');
                $query->orWhere('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.fonctions_specific.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.fonctions_specific.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = FonctionsSpecific::findOrFail($id);

        return view('legacy_migrated.fonctions_specific.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = FonctionsSpecific::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.fonctions_specific.edit', $item->id)
            ->with('success', 'FonctionsSpecific created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = FonctionsSpecific::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.fonctions_specific.edit', $item->id)
            ->with('success', 'FonctionsSpecific updated successfully');
    }
                

    public function destroy($id)
    {
        $item = FonctionsSpecific::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.fonctions_specific.index')
            ->with('success', 'FonctionsSpecific deleted successfully');
    }
                
}
