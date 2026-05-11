<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\TableauGardeStatus;
use Illuminate\Http\Request;

/**
 * Legacy migration source: tableau_garde_status.php
 * Legacy pattern: list
 * Legacy permission id: 5
 * This file stems from a legacy migration and requires functional verification.
 */
class TableauGardeStatusController extends Controller
{
    public function index(Request $request)
    {
        $query = TableauGardeStatus::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('eq_nom', 'like', '%' . $term . '%');
                $query->orWhere('e_code', 'like', '%' . $term . '%');
                $query->orWhere('p_id', 'like', '%' . $term . '%');
                $query->orWhere('p_nom', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.tableau_garde_status.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.tableau_garde_status.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = TableauGardeStatus::findOrFail($id);

        return view('legacy_migrated.tableau_garde_status.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mail' => 'nullable|string|max:255',
            'confirmed' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'month' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
            'equipe' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
        ]);

        $item = TableauGardeStatus::create([
            'mail' => $validated['mail'] ?? null,
            'confirmed' => $validated['confirmed'] ?? null,
            'action' => $validated['action'] ?? null,
            'month' => $validated['month'] ?? null,
            'year' => $validated['year'] ?? null,
            'equipe' => $validated['equipe'] ?? null,
            'filter' => $validated['filter'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.tableau_garde_status.edit', $item->id)
            ->with('success', 'TableauGardeStatus created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = TableauGardeStatus::findOrFail($id);

        $validated = $request->validate([
            'mail' => 'nullable|string|max:255',
            'confirmed' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'month' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
            'equipe' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
        ]);

        $item->update([
            'mail' => $validated['mail'] ?? null,
            'confirmed' => $validated['confirmed'] ?? null,
            'action' => $validated['action'] ?? null,
            'month' => $validated['month'] ?? null,
            'year' => $validated['year'] ?? null,
            'equipe' => $validated['equipe'] ?? null,
            'filter' => $validated['filter'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.tableau_garde_status.edit', $item->id)
            ->with('success', 'TableauGardeStatus updated successfully');
    }
                

    public function destroy($id)
    {
        $item = TableauGardeStatus::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.tableau_garde_status.index')
            ->with('success', 'TableauGardeStatus deleted successfully');
    }
                
}
