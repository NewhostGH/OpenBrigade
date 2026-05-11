<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\FonctionsImport;
use Illuminate\Http\Request;

/**
 * Legacy migration source: fonctions_import.php
 * Legacy pattern: list
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class FonctionsImportController extends Controller
{
    public function index(Request $request)
    {
        $query = FonctionsImport::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('count1', 'like', '%' . $term . '%');
                $query->orWhere('p_id', 'like', '%' . $term . '%');
                $query->orWhere('s_id', 'like', '%' . $term . '%');
                $query->orWhere('s_affiliation', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.fonctions_import.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.fonctions_import.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = FonctionsImport::findOrFail($id);

        return view('legacy_migrated.fonctions_import.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = FonctionsImport::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.fonctions_import.edit', $item->id)
            ->with('success', 'FonctionsImport created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = FonctionsImport::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.fonctions_import.edit', $item->id)
            ->with('success', 'FonctionsImport updated successfully');
    }
                

    public function destroy($id)
    {
        $item = FonctionsImport::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.fonctions_import.index')
            ->with('success', 'FonctionsImport deleted successfully');
    }
                
}
