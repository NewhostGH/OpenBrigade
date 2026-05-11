<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\DebugData;
use Illuminate\Http\Request;

/**
 * Legacy migration source: debug_data.php
 * Legacy pattern: generic
 * Legacy permission id: 14
 * This file stems from a legacy migration and requires functional verification.
 */
class DebugDataController extends Controller
{
    public function index(Request $request)
    {
        $query = DebugData::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.debug_data.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.debug_data.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = DebugData::findOrFail($id);

        return view('legacy_migrated.debug_data.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = DebugData::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.debug_data.edit', $item->id)
            ->with('success', 'DebugData created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = DebugData::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.debug_data.edit', $item->id)
            ->with('success', 'DebugData updated successfully');
    }
                

    public function destroy($id)
    {
        $item = DebugData::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.debug_data.index')
            ->with('success', 'DebugData deleted successfully');
    }
                
}
