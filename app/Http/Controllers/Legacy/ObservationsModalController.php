<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Observations;
use Illuminate\Http\Request;

/**
 * Legacy migration source: observations_modal.php
 * Legacy pattern: generic
 * Legacy permission id: 53
 * This file stems from a legacy migration and requires functional verification.
 */
class ObservationsModalController extends Controller
{
    public function index(Request $request)
    {
        $query = Observations::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
                $query->orWhere('observation', 'like', '%' . $term . '%');
                $query->orWhere('p_section', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.observations_modal.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.observations_modal.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Observations::findOrFail($id);

        return view('legacy_migrated.observations_modal.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'person' => 'nullable|string|max:255',
            'observation' => 'nullable|string|max:255',
        ]);

        $item = Observations::create([
            'person' => $validated['person'] ?? null,
            'observation' => $validated['observation'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.observations_modal.edit', $item->id)
            ->with('success', 'Observations created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Observations::findOrFail($id);

        $validated = $request->validate([
            'person' => 'nullable|string|max:255',
            'observation' => 'nullable|string|max:255',
        ]);

        $item->update([
            'person' => $validated['person'] ?? null,
            'observation' => $validated['observation'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.observations_modal.edit', $item->id)
            ->with('success', 'Observations updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Observations::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.observations_modal.index')
            ->with('success', 'Observations deleted successfully');
    }
                
}
