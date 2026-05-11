<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Vehicule;
use Illuminate\Http\Request;

/**
 * Legacy migration source: vehicule.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class VehiculeController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicule::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('type', 'like', '%' . $term . '%');
                $query->orWhere('immat', 'like', '%' . $term . '%');
                $query->orWhere('indicatif', 'like', '%' . $term . '%');
                $query->orWhere('section', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.vehicule.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.vehicule.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Vehicule::findOrFail($id);

        return view('legacy_migrated.vehicule.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'mad' => 'nullable|string|max:255',
            'dtdb' => 'nullable|string|max:255',
            'dtfn' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'filter2' => 'nullable|string|max:255',
            'vehicule' => 'nullable|string|max:255',
        ]);

        $item = Vehicule::create([
            'sub' => $validated['sub'] ?? null,
            'mad' => $validated['mad'] ?? null,
            'dtdb' => $validated['dtdb'] ?? null,
            'dtfn' => $validated['dtfn'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'filter2' => $validated['filter2'] ?? null,
            'vehicule' => $validated['vehicule'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.vehicule.edit', $item->id)
            ->with('success', 'Vehicule created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Vehicule::findOrFail($id);

        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'mad' => 'nullable|string|max:255',
            'dtdb' => 'nullable|string|max:255',
            'dtfn' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'filter2' => 'nullable|string|max:255',
            'vehicule' => 'nullable|string|max:255',
        ]);

        $item->update([
            'sub' => $validated['sub'] ?? null,
            'mad' => $validated['mad'] ?? null,
            'dtdb' => $validated['dtdb'] ?? null,
            'dtfn' => $validated['dtfn'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'filter2' => $validated['filter2'] ?? null,
            'vehicule' => $validated['vehicule'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.vehicule.edit', $item->id)
            ->with('success', 'Vehicule updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Vehicule::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.vehicule.index')
            ->with('success', 'Vehicule deleted successfully');
    }
                
}
