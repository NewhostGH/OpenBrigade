<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Http\Request;

/**
 * Legacy migration source: section.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class SectionController extends Controller
{
    public function index(Request $request)
    {
        $query = Section::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('count1', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.section.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.section.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Section::findOrFail($id);

        return view('legacy_migrated.section.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'displaytype' => 'nullable|string|max:255',
        ]);

        $item = Section::create([
            'displaytype' => $validated['displaytype'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.section.edit', $item->id)
            ->with('success', 'Section created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Section::findOrFail($id);

        $validated = $request->validate([
            'displaytype' => 'nullable|string|max:255',
        ]);

        $item->update([
            'displaytype' => $validated['displaytype'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.section.edit', $item->id)
            ->with('success', 'Section updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Section::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.section.index')
            ->with('success', 'Section deleted successfully');
    }
                
}
