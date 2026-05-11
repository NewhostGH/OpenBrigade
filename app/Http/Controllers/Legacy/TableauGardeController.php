<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\TableauGarde;
use Illuminate\Http\Request;

/**
 * Legacy migration source: tableau_garde.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class TableauGardeController extends Controller
{
    public function index(Request $request)
    {
        $query = TableauGarde::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('jour', 'like', '%' . $term . '%');
                $query->orWhere('s', 'like', '%' . $term . '%');
                $query->orWhere('poste_ni', 'like', '%' . $term . '%');
                $query->orWhere('eq_id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.tableau_garde.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.tableau_garde.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = TableauGarde::findOrFail($id);

        return view('legacy_migrated.tableau_garde.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'chk-masque' => 'nullable|string|max:255',
            'delete' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'equipe' => 'nullable|string|max:255',
            'tableau_garde_display_mode' => 'nullable|string|max:255',
            'month' => 'nullable|string|max:255',
            'week' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
            'person' => 'nullable|string|max:255',
        ]);

        $item = TableauGarde::create([
            'chk-masque' => $validated['chk-masque'] ?? null,
            'delete' => $validated['delete'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'equipe' => $validated['equipe'] ?? null,
            'tableau_garde_display_mode' => $validated['tableau_garde_display_mode'] ?? null,
            'month' => $validated['month'] ?? null,
            'week' => $validated['week'] ?? null,
            'year' => $validated['year'] ?? null,
            'person' => $validated['person'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.tableau_garde.edit', $item->id)
            ->with('success', 'TableauGarde created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = TableauGarde::findOrFail($id);

        $validated = $request->validate([
            'chk-masque' => 'nullable|string|max:255',
            'delete' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'equipe' => 'nullable|string|max:255',
            'tableau_garde_display_mode' => 'nullable|string|max:255',
            'month' => 'nullable|string|max:255',
            'week' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
            'person' => 'nullable|string|max:255',
        ]);

        $item->update([
            'chk-masque' => $validated['chk-masque'] ?? null,
            'delete' => $validated['delete'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'equipe' => $validated['equipe'] ?? null,
            'tableau_garde_display_mode' => $validated['tableau_garde_display_mode'] ?? null,
            'month' => $validated['month'] ?? null,
            'week' => $validated['week'] ?? null,
            'year' => $validated['year'] ?? null,
            'person' => $validated['person'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.tableau_garde.edit', $item->id)
            ->with('success', 'TableauGarde updated successfully');
    }
                

    public function destroy($id)
    {
        $item = TableauGarde::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.tableau_garde.index')
            ->with('success', 'TableauGarde deleted successfully');
    }
                
}
