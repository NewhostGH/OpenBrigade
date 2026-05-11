<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Remplacements;
use Illuminate\Http\Request;

/**
 * Legacy migration source: remplacements.php
 * Legacy pattern: list
 * Legacy permission id: 41
 * This file stems from a legacy migration and requires functional verification.
 */
class RemplacementsController extends Controller
{
    public function index(Request $request)
    {
        $query = Remplacements::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('datastylebtndefaultdatacontainerbodyechodisplay_children21', 'like', '%' . $term . '%');
                $query->orWhere('0', 'like', '%' . $term . '%');
                $query->orWhere('filter', 'like', '%' . $term . '%');
                $query->orWhere('nbmaxlevels', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.remplacements.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.remplacements.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Remplacements::findOrFail($id);

        return view('legacy_migrated.remplacements.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dtdb' => 'nullable|string|max:255',
            'dtfn' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'substitute' => 'nullable|string|max:255',
            'replaced' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
        ]);

        $item = Remplacements::create([
            'dtdb' => $validated['dtdb'] ?? null,
            'dtfn' => $validated['dtfn'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'substitute' => $validated['substitute'] ?? null,
            'replaced' => $validated['replaced'] ?? null,
            'status' => $validated['status'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.remplacements.edit', $item->id)
            ->with('success', 'Remplacements created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Remplacements::findOrFail($id);

        $validated = $request->validate([
            'dtdb' => 'nullable|string|max:255',
            'dtfn' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'substitute' => 'nullable|string|max:255',
            'replaced' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
        ]);

        $item->update([
            'dtdb' => $validated['dtdb'] ?? null,
            'dtfn' => $validated['dtfn'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'substitute' => $validated['substitute'] ?? null,
            'replaced' => $validated['replaced'] ?? null,
            'status' => $validated['status'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.remplacements.edit', $item->id)
            ->with('success', 'Remplacements updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Remplacements::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.remplacements.index')
            ->with('success', 'Remplacements deleted successfully');
    }
                
}
