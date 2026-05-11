<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\AstreintesUpdates;
use Illuminate\Http\Request;

/**
 * Legacy migration source: astreintes_updates.php
 * Legacy pattern: list
 * Legacy permission id: 14
 * This file stems from a legacy migration and requires functional verification.
 */
class AstreintesUpdatesController extends Controller
{
    public function index(Request $request)
    {
        $query = AstreintesUpdates::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('as_id', 'like', '%' . $term . '%');
                $query->orWhere('s_id', 'like', '%' . $term . '%');
                $query->orWhere('gp_id', 'like', '%' . $term . '%');
                $query->orWhere('p_id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.astreintes_updates.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.astreintes_updates.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = AstreintesUpdates::findOrFail($id);

        return view('legacy_migrated.astreintes_updates.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = AstreintesUpdates::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.astreintes_updates.edit', $item->id)
            ->with('success', 'AstreintesUpdates created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = AstreintesUpdates::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.astreintes_updates.edit', $item->id)
            ->with('success', 'AstreintesUpdates updated successfully');
    }
                

    public function destroy($id)
    {
        $item = AstreintesUpdates::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.astreintes_updates.index')
            ->with('success', 'AstreintesUpdates deleted successfully');
    }
                
}
