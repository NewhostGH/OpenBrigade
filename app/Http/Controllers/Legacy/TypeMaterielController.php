<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\TypeMateriel;
use Illuminate\Http\Request;

/**
 * Legacy migration source: type_materiel.php
 * Legacy pattern: list
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class TypeMaterielController extends Controller
{
    public function index(Request $request)
    {
        $query = TypeMateriel::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('cm_description', 'like', '%' . $term . '%');
                $query->orWhere('picture', 'like', '%' . $term . '%');
                $query->orWhere('tm_id', 'like', '%' . $term . '%');
                $query->orWhere('tm_code', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.type_materiel.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.type_materiel.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = TypeMateriel::findOrFail($id);

        return view('legacy_migrated.type_materiel.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'usage' => 'nullable|string|max:255',
        ]);

        $item = TypeMateriel::create([
            'usage' => $validated['usage'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.type_materiel.edit', $item->id)
            ->with('success', 'TypeMateriel created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = TypeMateriel::findOrFail($id);

        $validated = $request->validate([
            'usage' => 'nullable|string|max:255',
        ]);

        $item->update([
            'usage' => $validated['usage'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.type_materiel.edit', $item->id)
            ->with('success', 'TypeMateriel updated successfully');
    }
                

    public function destroy($id)
    {
        $item = TypeMateriel::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.type_materiel.index')
            ->with('success', 'TypeMateriel deleted successfully');
    }
                
}
