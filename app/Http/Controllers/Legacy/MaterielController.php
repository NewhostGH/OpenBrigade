<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Materiel;
use Illuminate\Http\Request;

/**
 * Legacy migration source: materiel.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class MaterielController extends Controller
{
    public function index(Request $request)
    {
        $query = Materiel::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('cat', 'like', '%' . $term . '%');
                $query->orWhere('type', 'like', '%' . $term . '%');
                $query->orWhere('lot', 'like', '%' . $term . '%');
                $query->orWhere('nb', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.materiel.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.materiel.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Materiel::findOrFail($id);

        return view('legacy_migrated.materiel.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'old' => 'nullable|string|max:255',
            'mad' => 'nullable|string|max:255',
            'dtdb' => 'nullable|string|max:255',
            'dtfn' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'type_materiel' => 'nullable|string|max:255',
            'menu1' => 'nullable|string|max:255',
        ]);

        $item = Materiel::create([
            'sub' => $validated['sub'] ?? null,
            'old' => $validated['old'] ?? null,
            'mad' => $validated['mad'] ?? null,
            'dtdb' => $validated['dtdb'] ?? null,
            'dtfn' => $validated['dtfn'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'type_materiel' => $validated['type_materiel'] ?? null,
            'menu1' => $validated['menu1'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.materiel.edit', $item->id)
            ->with('success', 'Materiel created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Materiel::findOrFail($id);

        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'old' => 'nullable|string|max:255',
            'mad' => 'nullable|string|max:255',
            'dtdb' => 'nullable|string|max:255',
            'dtfn' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'type_materiel' => 'nullable|string|max:255',
            'menu1' => 'nullable|string|max:255',
        ]);

        $item->update([
            'sub' => $validated['sub'] ?? null,
            'old' => $validated['old'] ?? null,
            'mad' => $validated['mad'] ?? null,
            'dtdb' => $validated['dtdb'] ?? null,
            'dtfn' => $validated['dtfn'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'type_materiel' => $validated['type_materiel'] ?? null,
            'menu1' => $validated['menu1'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.materiel.edit', $item->id)
            ->with('success', 'Materiel updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Materiel::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.materiel.index')
            ->with('success', 'Materiel deleted successfully');
    }
                
}
