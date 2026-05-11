<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\MenuStatusSet;
use Illuminate\Http\Request;

/**
 * Legacy migration source: menu_status_set.php
 * Legacy pattern: generic
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class MenuStatusSetController extends Controller
{
    public function index(Request $request)
    {
        $query = MenuStatusSet::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.menu_status_set.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.menu_status_set.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = MenuStatusSet::findOrFail($id);

        return view('legacy_migrated.menu_status_set.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'isCollapsed' => 'nullable|string|max:255',
        ]);

        $item = MenuStatusSet::create([
            'isCollapsed' => $validated['isCollapsed'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.menu_status_set.edit', $item->id)
            ->with('success', 'MenuStatusSet created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = MenuStatusSet::findOrFail($id);

        $validated = $request->validate([
            'isCollapsed' => 'nullable|string|max:255',
        ]);

        $item->update([
            'isCollapsed' => $validated['isCollapsed'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.menu_status_set.edit', $item->id)
            ->with('success', 'MenuStatusSet updated successfully');
    }
                

    public function destroy($id)
    {
        $item = MenuStatusSet::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.menu_status_set.index')
            ->with('success', 'MenuStatusSet deleted successfully');
    }
                
}
