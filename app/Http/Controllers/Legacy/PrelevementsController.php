<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Prelevements;
use Illuminate\Http\Request;

/**
 * Legacy migration source: prelevements.php
 * Legacy pattern: list
 * Legacy permission id: 53
 * This file stems from a legacy migration and requires functional verification.
 */
class PrelevementsController extends Controller
{
    public function index(Request $request)
    {
        $query = Prelevements::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_code', 'like', '%' . $term . '%');
                $query->orWhere('p_date', 'like', '%' . $term . '%');
                $query->orWhere('value', 'like', '%' . $term . '%');
                $query->orWhere('getelementbyidsub', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.prelevements.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.prelevements.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Prelevements::findOrFail($id);

        return view('legacy_migrated.prelevements.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'date_prelev' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
            'periode' => 'nullable|string|max:255',
            'subsections' => 'nullable|string|max:255',
        ]);

        $item = Prelevements::create([
            'sub' => $validated['sub'] ?? null,
            'date_prelev' => $validated['date_prelev'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'year' => $validated['year'] ?? null,
            'periode' => $validated['periode'] ?? null,
            'subsections' => $validated['subsections'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.prelevements.edit', $item->id)
            ->with('success', 'Prelevements created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Prelevements::findOrFail($id);

        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'date_prelev' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
            'periode' => 'nullable|string|max:255',
            'subsections' => 'nullable|string|max:255',
        ]);

        $item->update([
            'sub' => $validated['sub'] ?? null,
            'date_prelev' => $validated['date_prelev'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'year' => $validated['year'] ?? null,
            'periode' => $validated['periode'] ?? null,
            'subsections' => $validated['subsections'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.prelevements.edit', $item->id)
            ->with('success', 'Prelevements updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Prelevements::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.prelevements.index')
            ->with('success', 'Prelevements deleted successfully');
    }
                
}
