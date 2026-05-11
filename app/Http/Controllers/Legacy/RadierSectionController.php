<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\RadierSection;
use Illuminate\Http\Request;

/**
 * Legacy migration source: radier_section.php
 * Legacy pattern: list
 * Legacy permission id: 22
 * This file stems from a legacy migration and requires functional verification.
 */
class RadierSectionController extends Controller
{
    public function index(Request $request)
    {
        $query = RadierSection::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.radier_section.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.radier_section.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = RadierSection::findOrFail($id);

        return view('legacy_migrated.radier_section.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = RadierSection::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.radier_section.edit', $item->id)
            ->with('success', 'RadierSection created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = RadierSection::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.radier_section.edit', $item->id)
            ->with('success', 'RadierSection updated successfully');
    }
                

    public function destroy($id)
    {
        $item = RadierSection::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.radier_section.index')
            ->with('success', 'RadierSection deleted successfully');
    }
                
}
