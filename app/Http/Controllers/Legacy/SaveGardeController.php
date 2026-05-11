<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Garde;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_garde.php
 * Legacy pattern: save
 * Legacy permission id: 6
 * This file stems from a legacy migration and requires functional verification.
 */
class SaveGardeController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_garde.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Garde::findOrFail($id);

        return view('legacy_migrated.save_garde.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'evenement' => 'nullable|string|max:255',
        ]);

        $item = Garde::create([
            'evenement' => $validated['evenement'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_garde.edit', $item->id)
            ->with('success', 'Garde created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Garde::findOrFail($id);

        $validated = $request->validate([
            'evenement' => 'nullable|string|max:255',
        ]);

        $item->update([
            'evenement' => $validated['evenement'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_garde.edit', $item->id)
            ->with('success', 'Garde updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Garde::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_garde.index')
            ->with('success', 'Garde deleted successfully');
    }
                
}
