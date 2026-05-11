<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\HierarchieCompetence;
use Illuminate\Http\Request;

/**
 * Legacy migration source: hierarchie_competence.php
 * Legacy pattern: generic
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class HierarchieCompetenceController extends Controller
{
    public function index(Request $request)
    {
        $query = HierarchieCompetence::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('ph_code', 'like', '%' . $term . '%');
                $query->orWhere('ph_name', 'like', '%' . $term . '%');
                $query->orWhere('ph_hide_lower', 'like', '%' . $term . '%');
                $query->orWhere('ph_update_lower_expiry', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.hierarchie_competence.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.hierarchie_competence.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = HierarchieCompetence::findOrFail($id);

        return view('legacy_migrated.hierarchie_competence.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = HierarchieCompetence::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.hierarchie_competence.edit', $item->id)
            ->with('success', 'HierarchieCompetence created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = HierarchieCompetence::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.hierarchie_competence.edit', $item->id)
            ->with('success', 'HierarchieCompetence updated successfully');
    }
                

    public function destroy($id)
    {
        $item = HierarchieCompetence::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.hierarchie_competence.index')
            ->with('success', 'HierarchieCompetence deleted successfully');
    }
                
}
