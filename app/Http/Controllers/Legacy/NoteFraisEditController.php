<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\NoteFraisEdit;
use Illuminate\Http\Request;

/**
 * Legacy migration source: note_frais_edit.php
 * Legacy pattern: list
 * Legacy permission id: 77
 * This file stems from a legacy migration and requires functional verification.
 */
class NoteFraisEditController extends Controller
{
    public function index(Request $request)
    {
        $query = NoteFraisEdit::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('s_id', 'like', '%' . $term . '%');
                $query->orWhere('distincttf_code', 'like', '%' . $term . '%');
                $query->orWhere('tf_description', 'like', '%' . $term . '%');
                $query->orWhere('tf_categorie', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.note_frais_edit.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.note_frais_edit.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = NoteFraisEdit::findOrFail($id);

        return view('legacy_migrated.note_frais_edit.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'action' => 'nullable|string|max:255',
            'csrf_token_note' => 'nullable|string|max:255',
            'from' => 'nullable|string|max:255',
            'don' => 'nullable|string|max:255',
            'frais_dep' => 'nullable|string|max:255',
            'nfcode1' => 'nullable|string|max:255',
            'nfcode2' => 'nullable|string|max:255',
            'nfcode3' => 'nullable|string|max:255',
            'motif' => 'nullable|string|max:255',
            'national' => 'nullable|string|max:255',
            'departemental' => 'nullable|string|max:255',
            'syndicate' => 'nullable|string|max:255',
            'verified' => 'nullable|string|max:255',
            'userfile' => 'nullable|string|max:255',
            'justif_recus' => 'nullable|string|max:255',
            'update_detail' => 'nullable|string|max:255',
            'date' => 'nullable|string|max:255',
            'quantite' => 'nullable|string|max:255',
            'montant' => 'nullable|string|max:255',
            'lieu' => 'nullable|string|max:255',
        ]);

        $item = NoteFraisEdit::create([
            'action' => $validated['action'] ?? null,
            'csrf_token_note' => $validated['csrf_token_note'] ?? null,
            'from' => $validated['from'] ?? null,
            'don' => $validated['don'] ?? null,
            'frais_dep' => $validated['frais_dep'] ?? null,
            'nfcode1' => $validated['nfcode1'] ?? null,
            'nfcode2' => $validated['nfcode2'] ?? null,
            'nfcode3' => $validated['nfcode3'] ?? null,
            'motif' => $validated['motif'] ?? null,
            'national' => $validated['national'] ?? null,
            'departemental' => $validated['departemental'] ?? null,
            'syndicate' => $validated['syndicate'] ?? null,
            'verified' => $validated['verified'] ?? null,
            'userfile' => $validated['userfile'] ?? null,
            'justif_recus' => $validated['justif_recus'] ?? null,
            'update_detail' => $validated['update_detail'] ?? null,
            'date' => $validated['date'] ?? null,
            'quantite' => $validated['quantite'] ?? null,
            'montant' => $validated['montant'] ?? null,
            'lieu' => $validated['lieu'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.note_frais_edit.edit', $item->id)
            ->with('success', 'NoteFraisEdit created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = NoteFraisEdit::findOrFail($id);

        $validated = $request->validate([
            'action' => 'nullable|string|max:255',
            'csrf_token_note' => 'nullable|string|max:255',
            'from' => 'nullable|string|max:255',
            'don' => 'nullable|string|max:255',
            'frais_dep' => 'nullable|string|max:255',
            'nfcode1' => 'nullable|string|max:255',
            'nfcode2' => 'nullable|string|max:255',
            'nfcode3' => 'nullable|string|max:255',
            'motif' => 'nullable|string|max:255',
            'national' => 'nullable|string|max:255',
            'departemental' => 'nullable|string|max:255',
            'syndicate' => 'nullable|string|max:255',
            'verified' => 'nullable|string|max:255',
            'userfile' => 'nullable|string|max:255',
            'justif_recus' => 'nullable|string|max:255',
            'update_detail' => 'nullable|string|max:255',
            'date' => 'nullable|string|max:255',
            'quantite' => 'nullable|string|max:255',
            'montant' => 'nullable|string|max:255',
            'lieu' => 'nullable|string|max:255',
        ]);

        $item->update([
            'action' => $validated['action'] ?? null,
            'csrf_token_note' => $validated['csrf_token_note'] ?? null,
            'from' => $validated['from'] ?? null,
            'don' => $validated['don'] ?? null,
            'frais_dep' => $validated['frais_dep'] ?? null,
            'nfcode1' => $validated['nfcode1'] ?? null,
            'nfcode2' => $validated['nfcode2'] ?? null,
            'nfcode3' => $validated['nfcode3'] ?? null,
            'motif' => $validated['motif'] ?? null,
            'national' => $validated['national'] ?? null,
            'departemental' => $validated['departemental'] ?? null,
            'syndicate' => $validated['syndicate'] ?? null,
            'verified' => $validated['verified'] ?? null,
            'userfile' => $validated['userfile'] ?? null,
            'justif_recus' => $validated['justif_recus'] ?? null,
            'update_detail' => $validated['update_detail'] ?? null,
            'date' => $validated['date'] ?? null,
            'quantite' => $validated['quantite'] ?? null,
            'montant' => $validated['montant'] ?? null,
            'lieu' => $validated['lieu'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.note_frais_edit.edit', $item->id)
            ->with('success', 'NoteFraisEdit updated successfully');
    }
                

    public function destroy($id)
    {
        $item = NoteFraisEdit::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.note_frais_edit.index')
            ->with('success', 'NoteFraisEdit deleted successfully');
    }
                
}
