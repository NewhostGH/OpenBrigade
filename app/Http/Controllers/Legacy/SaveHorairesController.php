<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Horaires;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_horaires.php
 * Legacy pattern: save
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class SaveHorairesController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_horaires.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Horaires::findOrFail($id);

        return view('legacy_migrated.save_horaires.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'person' => 'nullable|string|max:255',
            'from' => 'nullable|string|max:255',
            'week' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
        ]);

        $item = Horaires::create([
            'person' => $validated['person'] ?? null,
            'from' => $validated['from'] ?? null,
            'week' => $validated['week'] ?? null,
            'year' => $validated['year'] ?? null,
            'status' => $validated['status'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_horaires.edit', $item->id)
            ->with('success', 'Horaires created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Horaires::findOrFail($id);

        $validated = $request->validate([
            'person' => 'nullable|string|max:255',
            'from' => 'nullable|string|max:255',
            'week' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
        ]);

        $item->update([
            'person' => $validated['person'] ?? null,
            'from' => $validated['from'] ?? null,
            'week' => $validated['week'] ?? null,
            'year' => $validated['year'] ?? null,
            'status' => $validated['status'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_horaires.edit', $item->id)
            ->with('success', 'Horaires updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Horaires::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_horaires.index')
            ->with('success', 'Horaires deleted successfully');
    }
                
}
