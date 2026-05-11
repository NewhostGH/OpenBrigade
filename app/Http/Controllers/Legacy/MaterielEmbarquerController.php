<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\MaterielEmbarquer;
use Illuminate\Http\Request;

/**
 * Legacy migration source: materiel_embarquer.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class MaterielEmbarquerController extends Controller
{
    public function index(Request $request)
    {
        $query = MaterielEmbarquer::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('value', 'like', '%' . $term . '%');
                $query->orWhere('from', 'like', '%' . $term . '%');
                $query->orWhere('eidiftypeallselectedselectedelseselectedechooptionvalueallselectedtoustypesdematrieloptionquery2selecttm_id', 'like', '%' . $term . '%');
                $query->orWhere('tm_code', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.materiel_embarquer.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.materiel_embarquer.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = MaterielEmbarquer::findOrFail($id);

        return view('legacy_migrated.materiel_embarquer.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'nullable|string|max:255',
            'addmateriel' => 'nullable|string|max:255',
            'addconsommable' => 'nullable|string|max:255',
        ]);

        $item = MaterielEmbarquer::create([
            'type' => $validated['type'] ?? null,
            'addmateriel' => $validated['addmateriel'] ?? null,
            'addconsommable' => $validated['addconsommable'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.materiel_embarquer.edit', $item->id)
            ->with('success', 'MaterielEmbarquer created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = MaterielEmbarquer::findOrFail($id);

        $validated = $request->validate([
            'type' => 'nullable|string|max:255',
            'addmateriel' => 'nullable|string|max:255',
            'addconsommable' => 'nullable|string|max:255',
        ]);

        $item->update([
            'type' => $validated['type'] ?? null,
            'addmateriel' => $validated['addmateriel'] ?? null,
            'addconsommable' => $validated['addconsommable'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.materiel_embarquer.edit', $item->id)
            ->with('success', 'MaterielEmbarquer updated successfully');
    }
                

    public function destroy($id)
    {
        $item = MaterielEmbarquer::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.materiel_embarquer.index')
            ->with('success', 'MaterielEmbarquer deleted successfully');
    }
                
}
