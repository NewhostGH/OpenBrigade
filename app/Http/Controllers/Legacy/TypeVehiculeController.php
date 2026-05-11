<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\TypeVehicule;
use Illuminate\Http\Request;

/**
 * Legacy migration source: type_vehicule.php
 * Legacy pattern: list
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class TypeVehiculeController extends Controller
{
    public function index(Request $request)
    {
        $query = TypeVehicule::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('tv_icon', 'like', '%' . $term . '%');
                $query->orWhere('tv_code', 'like', '%' . $term . '%');
                $query->orWhere('tv_libelle', 'like', '%' . $term . '%');
                $query->orWhere('tv_nb', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.type_vehicule.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.type_vehicule.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = TypeVehicule::findOrFail($id);

        return view('legacy_migrated.type_vehicule.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = TypeVehicule::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.type_vehicule.edit', $item->id)
            ->with('success', 'TypeVehicule created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = TypeVehicule::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.type_vehicule.edit', $item->id)
            ->with('success', 'TypeVehicule updated successfully');
    }
                

    public function destroy($id)
    {
        $item = TypeVehicule::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.type_vehicule.index')
            ->with('success', 'TypeVehicule deleted successfully');
    }
                
}
