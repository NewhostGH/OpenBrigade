<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EvenementDiplome;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_diplome.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementDiplomeController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementDiplome::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('s_id', 'like', '%' . $term . '%');
                $query->orWhere('ps_id', 'like', '%' . $term . '%');
                $query->orWhere('type', 'like', '%' . $term . '%');
                $query->orWhere('eh_date_debut', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.evenement_diplome.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.evenement_diplome.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EvenementDiplome::findOrFail($id);

        return view('legacy_migrated.evenement_diplome.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'evenement' => 'nullable|string|max:255',
            'expiration' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:255',
            'update_hierarchy' => 'nullable|string|max:255',
        ]);

        $item = EvenementDiplome::create([
            'evenement' => $validated['evenement'] ?? null,
            'expiration' => $validated['expiration'] ?? null,
            'comment' => $validated['comment'] ?? null,
            'update_hierarchy' => $validated['update_hierarchy'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_diplome.edit', $item->id)
            ->with('success', 'EvenementDiplome created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EvenementDiplome::findOrFail($id);

        $validated = $request->validate([
            'evenement' => 'nullable|string|max:255',
            'expiration' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:255',
            'update_hierarchy' => 'nullable|string|max:255',
        ]);

        $item->update([
            'evenement' => $validated['evenement'] ?? null,
            'expiration' => $validated['expiration'] ?? null,
            'comment' => $validated['comment'] ?? null,
            'update_hierarchy' => $validated['update_hierarchy'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_diplome.edit', $item->id)
            ->with('success', 'EvenementDiplome updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EvenementDiplome::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenement_diplome.index')
            ->with('success', 'EvenementDiplome deleted successfully');
    }
                
}
