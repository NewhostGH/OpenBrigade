<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Astreintes;
use Illuminate\Http\Request;

/**
 * Legacy migration source: astreintes.php
 * Legacy pattern: list
 * Legacy permission id: 52
 * This file stems from a legacy migration and requires functional verification.
 */
class AstreintesController extends Controller
{
    public function index(Request $request)
    {
        $query = Astreintes::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('distinctgp_id', 'like', '%' . $term . '%');
                $query->orWhere('gp_description', 'like', '%' . $term . '%');
                $query->orWhere('as_id', 'like', '%' . $term . '%');
                $query->orWhere('s_id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.astreintes.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.astreintes.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Astreintes::findOrFail($id);

        return view('legacy_migrated.astreintes.form', [
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
            'type_astreinte' => 'nullable|string|max:255',
            'person' => 'nullable|string|max:255',
        ]);

        $item = Astreintes::create([
            'sub' => $validated['sub'] ?? null,
            'dtdb' => $validated['dtdb'] ?? null,
            'dtfn' => $validated['dtfn'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'type_astreinte' => $validated['type_astreinte'] ?? null,
            'person' => $validated['person'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.astreintes.edit', $item->id)
            ->with('success', 'Astreintes created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Astreintes::findOrFail($id);

        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'dtdb' => 'nullable|string|max:255',
            'dtfn' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'type_astreinte' => 'nullable|string|max:255',
            'person' => 'nullable|string|max:255',
        ]);

        $item->update([
            'sub' => $validated['sub'] ?? null,
            'dtdb' => $validated['dtdb'] ?? null,
            'dtfn' => $validated['dtfn'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'type_astreinte' => $validated['type_astreinte'] ?? null,
            'person' => $validated['person'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.astreintes.edit', $item->id)
            ->with('success', 'Astreintes updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Astreintes::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.astreintes.index')
            ->with('success', 'Astreintes deleted successfully');
    }
                
}
