<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\RebuildSectionFlat;
use Illuminate\Http\Request;

/**
 * Legacy migration source: rebuild_section_flat.php
 * Legacy pattern: generic
 * Legacy permission id: 14
 * This file stems from a legacy migration and requires functional verification.
 */
class RebuildSectionFlatController extends Controller
{
    public function index(Request $request)
    {
        $query = RebuildSectionFlat::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.rebuild_section_flat.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.rebuild_section_flat.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = RebuildSectionFlat::findOrFail($id);

        return view('legacy_migrated.rebuild_section_flat.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = RebuildSectionFlat::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.rebuild_section_flat.edit', $item->id)
            ->with('success', 'RebuildSectionFlat created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = RebuildSectionFlat::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.rebuild_section_flat.edit', $item->id)
            ->with('success', 'RebuildSectionFlat updated successfully');
    }
                

    public function destroy($id)
    {
        $item = RebuildSectionFlat::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.rebuild_section_flat.index')
            ->with('success', 'RebuildSectionFlat deleted successfully');
    }
                
}
