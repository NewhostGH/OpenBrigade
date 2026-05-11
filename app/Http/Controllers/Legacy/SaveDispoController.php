<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Dispo;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_dispo.php
 * Legacy pattern: save
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class SaveDispoController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_dispo.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Dispo::findOrFail($id);

        return view('legacy_migrated.save_dispo.form', [
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
            'msg' => 'nullable|string|max:255',
        ]);

        $item = Dispo::create([
            'nbjours' => $validated['nbjours'] ?? null,
            'month' => $validated['month'] ?? null,
            'year' => $validated['year'] ?? null,
            'person' => $validated['person'] ?? null,
            'msg' => $validated['msg'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_dispo.edit', $item->id)
            ->with('success', 'Dispo created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Dispo::findOrFail($id);

        $validated = $request->validate([
            'nbjours' => 'nullable|string|max:255',
            'month' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
            'person' => 'nullable|string|max:255',
            'msg' => 'nullable|string|max:255',
        ]);

        $item->update([
            'nbjours' => $validated['nbjours'] ?? null,
            'month' => $validated['month'] ?? null,
            'year' => $validated['year'] ?? null,
            'person' => $validated['person'] ?? null,
            'msg' => $validated['msg'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_dispo.edit', $item->id)
            ->with('success', 'Dispo updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Dispo::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_dispo.index')
            ->with('success', 'Dispo deleted successfully');
    }
                
}
