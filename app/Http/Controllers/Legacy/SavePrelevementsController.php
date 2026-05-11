<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Prelevements;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_prelevements.php
 * Legacy pattern: save
 * Legacy permission id: 53
 * This file stems from a legacy migration and requires functional verification.
 */
class SavePrelevementsController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_prelevements.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Prelevements::findOrFail($id);

        return view('legacy_migrated.save_prelevements.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'filter' => 'nullable|string|max:255',
            'subsections' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
            'periode' => 'nullable|string|max:255',
            'date_prelev' => 'nullable|string|max:255',
        ]);

        $item = Prelevements::create([
            'filter' => $validated['filter'] ?? null,
            'subsections' => $validated['subsections'] ?? null,
            'year' => $validated['year'] ?? null,
            'periode' => $validated['periode'] ?? null,
            'date_prelev' => $validated['date_prelev'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_prelevements.edit', $item->id)
            ->with('success', 'Prelevements created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Prelevements::findOrFail($id);

        $validated = $request->validate([
            'filter' => 'nullable|string|max:255',
            'subsections' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
            'periode' => 'nullable|string|max:255',
            'date_prelev' => 'nullable|string|max:255',
        ]);

        $item->update([
            'filter' => $validated['filter'] ?? null,
            'subsections' => $validated['subsections'] ?? null,
            'year' => $validated['year'] ?? null,
            'periode' => $validated['periode'] ?? null,
            'date_prelev' => $validated['date_prelev'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_prelevements.edit', $item->id)
            ->with('success', 'Prelevements updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Prelevements::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_prelevements.index')
            ->with('success', 'Prelevements deleted successfully');
    }
                
}
