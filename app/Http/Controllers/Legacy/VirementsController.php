<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Virements;
use Illuminate\Http\Request;

/**
 * Legacy migration source: virements.php
 * Legacy pattern: list
 * Legacy permission id: 53
 * This file stems from a legacy migration and requires functional verification.
 */
class VirementsController extends Controller
{
    public function index(Request $request)
    {
        $query = Virements::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('value', 'like', '%' . $term . '%');
                $query->orWhere('getelementbyidsub', 'like', '%' . $term . '%');
                $query->orWhere('getelementbyidinclude_old', 'like', '%' . $term . '%');
                $query->orWhere('display_children21', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.virements.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.virements.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Virements::findOrFail($id);

        return view('legacy_migrated.virements.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'include_old' => 'nullable|string|max:255',
            'dtdb' => 'nullable|string|max:255',
            'dtfn' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'compte_a_debiter' => 'nullable|string|max:255',
        ]);

        $item = Virements::create([
            'sub' => $validated['sub'] ?? null,
            'include_old' => $validated['include_old'] ?? null,
            'dtdb' => $validated['dtdb'] ?? null,
            'dtfn' => $validated['dtfn'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'compte_a_debiter' => $validated['compte_a_debiter'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.virements.edit', $item->id)
            ->with('success', 'Virements created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Virements::findOrFail($id);

        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'include_old' => 'nullable|string|max:255',
            'dtdb' => 'nullable|string|max:255',
            'dtfn' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'compte_a_debiter' => 'nullable|string|max:255',
        ]);

        $item->update([
            'sub' => $validated['sub'] ?? null,
            'include_old' => $validated['include_old'] ?? null,
            'dtdb' => $validated['dtdb'] ?? null,
            'dtfn' => $validated['dtfn'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'compte_a_debiter' => $validated['compte_a_debiter'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.virements.edit', $item->id)
            ->with('success', 'Virements updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Virements::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.virements.index')
            ->with('success', 'Virements deleted successfully');
    }
                
}
