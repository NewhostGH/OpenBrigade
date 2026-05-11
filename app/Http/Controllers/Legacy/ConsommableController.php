<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Consommable;
use Illuminate\Http\Request;

/**
 * Legacy migration source: consommable.php
 * Legacy pattern: list
 * Legacy permission id: 42
 * This file stems from a legacy migration and requires functional verification.
 */
class ConsommableController extends Controller
{
    public function index(Request $request)
    {
        $query = Consommable::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('catgorie', 'like', '%' . $term . '%');
                $query->orWhere('type', 'like', '%' . $term . '%');
                $query->orWhere('stock', 'like', '%' . $term . '%');
                $query->orWhere('min', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.consommable.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.consommable.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Consommable::findOrFail($id);

        return view('legacy_migrated.consommable.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'dtdb' => 'nullable|string|max:255',
            'dtfn' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'type_conso' => 'nullable|string|max:255',
        ]);

        $item = Consommable::create([
            'sub' => $validated['sub'] ?? null,
            'dtdb' => $validated['dtdb'] ?? null,
            'dtfn' => $validated['dtfn'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'type_conso' => $validated['type_conso'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.consommable.edit', $item->id)
            ->with('success', 'Consommable created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Consommable::findOrFail($id);

        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'dtdb' => 'nullable|string|max:255',
            'dtfn' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'type_conso' => 'nullable|string|max:255',
        ]);

        $item->update([
            'sub' => $validated['sub'] ?? null,
            'dtdb' => $validated['dtdb'] ?? null,
            'dtfn' => $validated['dtfn'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'type_conso' => $validated['type_conso'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.consommable.edit', $item->id)
            ->with('success', 'Consommable updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Consommable::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.consommable.index')
            ->with('success', 'Consommable deleted successfully');
    }
                
}
