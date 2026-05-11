<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Http\Request;

/**
 * Legacy migration source: del_section.php
 * Legacy pattern: delete
 * Legacy permission id: 19
 * This file stems from a legacy migration and requires functional verification.
 */
class DelSectionController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.del_section.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Section::findOrFail($id);

        return view('legacy_migrated.del_section.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Section::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_section.edit', $item->id)
            ->with('success', 'Section created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Section::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_section.edit', $item->id)
            ->with('success', 'Section updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Section::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.del_section.index')
            ->with('success', 'Section deleted successfully');
    }
                
}
