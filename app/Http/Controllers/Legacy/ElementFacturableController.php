<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\ElementFacturable;
use Illuminate\Http\Request;

/**
 * Legacy migration source: element_facturable.php
 * Legacy pattern: list
 * Legacy permission id: 29
 * This file stems from a legacy migration and requires functional verification.
 */
class ElementFacturableController extends Controller
{
    public function index(Request $request)
    {
        $query = ElementFacturable::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('count', 'like', '%' . $term . '%');
                $query->orWhere('tef_code', 'like', '%' . $term . '%');
                $query->orWhere('tef_name', 'like', '%' . $term . '%');
                $query->orWhere('s_id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.element_facturable.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.element_facturable.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = ElementFacturable::findOrFail($id);

        return view('legacy_migrated.element_facturable.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'annuler' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'type_element' => 'nullable|string|max:255',
        ]);

        $item = ElementFacturable::create([
            'annuler' => $validated['annuler'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'type_element' => $validated['type_element'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.element_facturable.edit', $item->id)
            ->with('success', 'ElementFacturable created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = ElementFacturable::findOrFail($id);

        $validated = $request->validate([
            'annuler' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'type_element' => 'nullable|string|max:255',
        ]);

        $item->update([
            'annuler' => $validated['annuler'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'type_element' => $validated['type_element'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.element_facturable.edit', $item->id)
            ->with('success', 'ElementFacturable updated successfully');
    }
                

    public function destroy($id)
    {
        $item = ElementFacturable::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.element_facturable.index')
            ->with('success', 'ElementFacturable deleted successfully');
    }
                
}
