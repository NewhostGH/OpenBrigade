<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Piquet;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_piquet.php
 * Legacy pattern: save
 * Legacy permission id: 6
 * This file stems from a legacy migration and requires functional verification.
 */
class SavePiquetController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_piquet.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Piquet::findOrFail($id);

        return view('legacy_migrated.save_piquet.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Piquet::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.save_piquet.edit', $item->id)
            ->with('success', 'Piquet created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Piquet::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.save_piquet.edit', $item->id)
            ->with('success', 'Piquet updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Piquet::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_piquet.index')
            ->with('success', 'Piquet deleted successfully');
    }
                
}
