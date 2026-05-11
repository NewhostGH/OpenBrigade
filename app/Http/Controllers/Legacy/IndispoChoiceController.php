<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\IndispoChoice;
use Illuminate\Http\Request;

/**
 * Legacy migration source: indispo_choice.php
 * Legacy pattern: list
 * Legacy permission id: 11
 * This file stems from a legacy migration and requires functional verification.
 */
class IndispoChoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = IndispoChoice::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('dure', 'like', '%' . $term . '%');
                $query->orWhere('jours_type_indispo', 'like', '%' . $term . '%');
                $query->orWhere('i_code', 'like', '%' . $term . '%');
                $query->orWhere('p_id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.indispo_choice.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.indispo_choice.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = IndispoChoice::findOrFail($id);

        return view('legacy_migrated.indispo_choice.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tab' => 'nullable|string|max:255',
            'sub' => 'nullable|string|max:255',
            'dtdb' => 'nullable|string|max:255',
            'dtfn' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'menu1' => 'nullable|string|max:255',
            'menu3' => 'nullable|string|max:255',
            'menu2' => 'nullable|string|max:255',
            'menu5' => 'nullable|string|max:255',
        ]);

        $item = IndispoChoice::create([
            'tab' => $validated['tab'] ?? null,
            'sub' => $validated['sub'] ?? null,
            'dtdb' => $validated['dtdb'] ?? null,
            'dtfn' => $validated['dtfn'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'menu1' => $validated['menu1'] ?? null,
            'menu3' => $validated['menu3'] ?? null,
            'menu2' => $validated['menu2'] ?? null,
            'menu5' => $validated['menu5'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.indispo_choice.edit', $item->id)
            ->with('success', 'IndispoChoice created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = IndispoChoice::findOrFail($id);

        $validated = $request->validate([
            'tab' => 'nullable|string|max:255',
            'sub' => 'nullable|string|max:255',
            'dtdb' => 'nullable|string|max:255',
            'dtfn' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'menu1' => 'nullable|string|max:255',
            'menu3' => 'nullable|string|max:255',
            'menu2' => 'nullable|string|max:255',
            'menu5' => 'nullable|string|max:255',
        ]);

        $item->update([
            'tab' => $validated['tab'] ?? null,
            'sub' => $validated['sub'] ?? null,
            'dtdb' => $validated['dtdb'] ?? null,
            'dtfn' => $validated['dtfn'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'menu1' => $validated['menu1'] ?? null,
            'menu3' => $validated['menu3'] ?? null,
            'menu2' => $validated['menu2'] ?? null,
            'menu5' => $validated['menu5'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.indispo_choice.edit', $item->id)
            ->with('success', 'IndispoChoice updated successfully');
    }
                

    public function destroy($id)
    {
        $item = IndispoChoice::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.indispo_choice.index')
            ->with('success', 'IndispoChoice deleted successfully');
    }
                
}
