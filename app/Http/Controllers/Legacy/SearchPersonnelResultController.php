<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\SearchPersonnelResult;
use Illuminate\Http\Request;

/**
 * Legacy migration source: search_personnel_result.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class SearchPersonnelResultController extends Controller
{
    public function index(Request $request)
    {
        $query = SearchPersonnelResult::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('nom_prnom', 'like', '%' . $term . '%');
                $query->orWhere('identifiant', 'like', '%' . $term . '%');
                $query->orWhere('section', 'like', '%' . $term . '%');
                $query->orWhere('statut', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.search_personnel_result.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.search_personnel_result.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = SearchPersonnelResult::findOrFail($id);

        return view('legacy_migrated.search_personnel_result.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'SelectionMail' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
            'typetri' => 'nullable|string|max:255',
            'statut' => 'nullable|string|max:255',
            'trouve' => 'nullable|string|max:255',
            'qualif' => 'nullable|string|max:255',
        ]);

        $item = SearchPersonnelResult::create([
            'SelectionMail' => $validated['SelectionMail'] ?? null,
            'section' => $validated['section'] ?? null,
            'typetri' => $validated['typetri'] ?? null,
            'statut' => $validated['statut'] ?? null,
            'trouve' => $validated['trouve'] ?? null,
            'qualif' => $validated['qualif'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.search_personnel_result.edit', $item->id)
            ->with('success', 'SearchPersonnelResult created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = SearchPersonnelResult::findOrFail($id);

        $validated = $request->validate([
            'SelectionMail' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
            'typetri' => 'nullable|string|max:255',
            'statut' => 'nullable|string|max:255',
            'trouve' => 'nullable|string|max:255',
            'qualif' => 'nullable|string|max:255',
        ]);

        $item->update([
            'SelectionMail' => $validated['SelectionMail'] ?? null,
            'section' => $validated['section'] ?? null,
            'typetri' => $validated['typetri'] ?? null,
            'statut' => $validated['statut'] ?? null,
            'trouve' => $validated['trouve'] ?? null,
            'qualif' => $validated['qualif'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.search_personnel_result.edit', $item->id)
            ->with('success', 'SearchPersonnelResult updated successfully');
    }
                

    public function destroy($id)
    {
        $item = SearchPersonnelResult::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.search_personnel_result.index')
            ->with('success', 'SearchPersonnelResult deleted successfully');
    }
                
}
