<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Repos;
use Illuminate\Http\Request;

/**
 * Legacy migration source: repos_save.php
 * Legacy pattern: save
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class ReposSaveController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.repos_save.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Repos::findOrFail($id);

        return view('legacy_migrated.repos_save.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nbjours' => 'nullable|string|max:255',
            'month' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
            'person' => 'nullable|string|max:255',
        ]);

        $item = Repos::create([
            'nbjours' => $validated['nbjours'] ?? null,
            'month' => $validated['month'] ?? null,
            'year' => $validated['year'] ?? null,
            'person' => $validated['person'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.repos_save.edit', $item->id)
            ->with('success', 'Repos created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Repos::findOrFail($id);

        $validated = $request->validate([
            'nbjours' => 'nullable|string|max:255',
            'month' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
            'person' => 'nullable|string|max:255',
        ]);

        $item->update([
            'nbjours' => $validated['nbjours'] ?? null,
            'month' => $validated['month'] ?? null,
            'year' => $validated['year'] ?? null,
            'person' => $validated['person'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.repos_save.edit', $item->id)
            ->with('success', 'Repos updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Repos::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.repos_save.index')
            ->with('success', 'Repos deleted successfully');
    }
                
}
