<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\GradesLoad;
use Illuminate\Http\Request;

/**
 * Legacy migration source: grades_load.php
 * Legacy pattern: generic
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class GradesLoadController extends Controller
{
    public function index(Request $request)
    {
        $query = GradesLoad::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.grades_load.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.grades_load.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = GradesLoad::findOrFail($id);

        return view('legacy_migrated.grades_load.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = GradesLoad::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.grades_load.edit', $item->id)
            ->with('success', 'GradesLoad created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = GradesLoad::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.grades_load.edit', $item->id)
            ->with('success', 'GradesLoad updated successfully');
    }
                

    public function destroy($id)
    {
        $item = GradesLoad::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.grades_load.index')
            ->with('success', 'GradesLoad deleted successfully');
    }
                
}
