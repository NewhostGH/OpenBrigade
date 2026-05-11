<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\TypeGarde;
use Illuminate\Http\Request;

/**
 * Legacy migration source: type_garde.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class TypeGardeController extends Controller
{
    public function index(Request $request)
    {
        $query = TypeGarde::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('eq_id', 'like', '%' . $term . '%');
                $query->orWhere('eq_nom', 'like', '%' . $term . '%');
                $query->orWhere('eq_jour', 'like', '%' . $term . '%');
                $query->orWhere('eq_nuit', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.type_garde.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.type_garde.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = TypeGarde::findOrFail($id);

        return view('legacy_migrated.type_garde.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'filter' => 'nullable|string|max:255',
        ]);

        $item = TypeGarde::create([
            'filter' => $validated['filter'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.type_garde.edit', $item->id)
            ->with('success', 'TypeGarde created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = TypeGarde::findOrFail($id);

        $validated = $request->validate([
            'filter' => 'nullable|string|max:255',
        ]);

        $item->update([
            'filter' => $validated['filter'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.type_garde.edit', $item->id)
            ->with('success', 'TypeGarde updated successfully');
    }
                

    public function destroy($id)
    {
        $item = TypeGarde::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.type_garde.index')
            ->with('success', 'TypeGarde deleted successfully');
    }
                
}
