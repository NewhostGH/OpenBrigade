<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Map;
use Illuminate\Http\Request;

/**
 * Legacy migration source: map.php
 * Legacy pattern: generic
 * Legacy permission id: 76
 * This file stems from a legacy migration and requires functional verification.
 */
class MapController extends Controller
{
    public function index(Request $request)
    {
        $query = Map::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('e_addressaddress', 'like', '%' . $term . '%');
                $query->orWhere('lat', 'like', '%' . $term . '%');
                $query->orWhere('lng', 'like', '%' . $term . '%');
                $query->orWhere('p_address', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.map.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.map.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Map::findOrFail($id);

        return view('legacy_migrated.map.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Map::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.map.edit', $item->id)
            ->with('success', 'Map created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Map::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.map.edit', $item->id)
            ->with('success', 'Map updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Map::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.map.index')
            ->with('success', 'Map deleted successfully');
    }
                
}
