<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\FonctionsGardes;
use Illuminate\Http\Request;

/**
 * Legacy migration source: fonctions_gardes.php
 * Legacy pattern: list
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class FonctionsGardesController extends Controller
{
    public function index(Request $request)
    {
        $query = FonctionsGardes::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('date', 'like', '%' . $term . '%');
                $query->orWhere('activit', 'like', '%' . $term . '%');
                $query->orWhere('a_remplacer', 'like', '%' . $term . '%');
                $query->orWhere('remplaant_propos', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.fonctions_gardes.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.fonctions_gardes.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = FonctionsGardes::findOrFail($id);

        return view('legacy_migrated.fonctions_gardes.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'check1_' => 'nullable|string|max:255',
            'check2_' => 'nullable|string|max:255',
        ]);

        $item = FonctionsGardes::create([
            'check1_' => $validated['check1_'] ?? null,
            'check2_' => $validated['check2_'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.fonctions_gardes.edit', $item->id)
            ->with('success', 'FonctionsGardes created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = FonctionsGardes::findOrFail($id);

        $validated = $request->validate([
            'check1_' => 'nullable|string|max:255',
            'check2_' => 'nullable|string|max:255',
        ]);

        $item->update([
            'check1_' => $validated['check1_'] ?? null,
            'check2_' => $validated['check2_'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.fonctions_gardes.edit', $item->id)
            ->with('success', 'FonctionsGardes updated successfully');
    }
                

    public function destroy($id)
    {
        $item = FonctionsGardes::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.fonctions_gardes.index')
            ->with('success', 'FonctionsGardes deleted successfully');
    }
                
}
