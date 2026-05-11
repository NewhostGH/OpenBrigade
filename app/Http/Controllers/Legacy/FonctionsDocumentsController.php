<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\FonctionsDocuments;
use Illuminate\Http\Request;

/**
 * Legacy migration source: fonctions_documents.php
 * Legacy pattern: list
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class FonctionsDocumentsController extends Controller
{
    public function index(Request $request)
    {
        $query = FonctionsDocuments::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('ds_id', 'like', '%' . $term . '%');
                $query->orWhere('ds_libelle', 'like', '%' . $term . '%');
                $query->orWhere('f_id', 'like', '%' . $term . '%');
                $query->orWhere('d_id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.fonctions_documents.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.fonctions_documents.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = FonctionsDocuments::findOrFail($id);

        return view('legacy_migrated.fonctions_documents.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = FonctionsDocuments::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.fonctions_documents.edit', $item->id)
            ->with('success', 'FonctionsDocuments created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = FonctionsDocuments::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.fonctions_documents.edit', $item->id)
            ->with('success', 'FonctionsDocuments updated successfully');
    }
                

    public function destroy($id)
    {
        $item = FonctionsDocuments::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.fonctions_documents.index')
            ->with('success', 'FonctionsDocuments deleted successfully');
    }
                
}
