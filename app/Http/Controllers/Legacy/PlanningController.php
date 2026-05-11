<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Planning;
use Illuminate\Http\Request;

/**
 * Legacy migration source: planning.php
 * Legacy pattern: list
 * Legacy permission id: 56
 * This file stems from a legacy migration and requires functional verification.
 */
class PlanningController extends Controller
{
    public function index(Request $request)
    {
        $query = Planning::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('d', 'like', '%' . $term . '%');
                $query->orWhere('count1', 'like', '%' . $term . '%');
                $query->orWhere('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.planning.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.planning.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Planning::findOrFail($id);

        return view('legacy_migrated.planning.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'day_planning' => 'nullable|string|max:255',
            'menu2' => 'nullable|string|max:255',
            'menu1' => 'nullable|string|max:255',
        ]);

        $item = Planning::create([
            'sub' => $validated['sub'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'type' => $validated['type'] ?? null,
            'day_planning' => $validated['day_planning'] ?? null,
            'menu2' => $validated['menu2'] ?? null,
            'menu1' => $validated['menu1'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.planning.edit', $item->id)
            ->with('success', 'Planning created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Planning::findOrFail($id);

        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'day_planning' => 'nullable|string|max:255',
            'menu2' => 'nullable|string|max:255',
            'menu1' => 'nullable|string|max:255',
        ]);

        $item->update([
            'sub' => $validated['sub'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'type' => $validated['type'] ?? null,
            'day_planning' => $validated['day_planning'] ?? null,
            'menu2' => $validated['menu2'] ?? null,
            'menu1' => $validated['menu1'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.planning.edit', $item->id)
            ->with('success', 'Planning updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Planning::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.planning.index')
            ->with('success', 'Planning deleted successfully');
    }
                
}
