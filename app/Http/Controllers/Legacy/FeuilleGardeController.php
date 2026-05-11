<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\FeuilleGarde;
use Illuminate\Http\Request;

/**
 * Legacy migration source: feuille_garde.php
 * Legacy pattern: list
 * Legacy permission id: 61
 * This file stems from a legacy migration and requires functional verification.
 */
class FeuilleGardeController extends Controller
{
    public function index(Request $request)
    {
        $query = FeuilleGarde::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('value', 'like', '%' . $term . '%');
                $query->orWhere('ifpompiersmaxlnbmaxlevels1elsemaxlnbmaxlevelsdisplay_children21', 'like', '%' . $term . '%');
                $query->orWhere('0', 'like', '%' . $term . '%');
                $query->orWhere('filter', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.feuille_garde.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.feuille_garde.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = FeuilleGarde::findOrFail($id);

        return view('legacy_migrated.feuille_garde.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'filter' => 'nullable|string|max:255',
        ]);

        $item = FeuilleGarde::create([
            'filter' => $validated['filter'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.feuille_garde.edit', $item->id)
            ->with('success', 'FeuilleGarde created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = FeuilleGarde::findOrFail($id);

        $validated = $request->validate([
            'filter' => 'nullable|string|max:255',
        ]);

        $item->update([
            'filter' => $validated['filter'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.feuille_garde.edit', $item->id)
            ->with('success', 'FeuilleGarde updated successfully');
    }
                

    public function destroy($id)
    {
        $item = FeuilleGarde::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.feuille_garde.index')
            ->with('success', 'FeuilleGarde deleted successfully');
    }
                
}
