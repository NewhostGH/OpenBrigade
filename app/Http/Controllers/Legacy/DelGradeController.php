<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use Illuminate\Http\Request;

/**
 * Legacy migration source: del_grade.php
 * Legacy pattern: delete
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class DelGradeController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.del_grade.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Grade::findOrFail($id);

        return view('legacy_migrated.del_grade.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Grade::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_grade.edit', $item->id)
            ->with('success', 'Grade created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Grade::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_grade.edit', $item->id)
            ->with('success', 'Grade updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Grade::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.del_grade.index')
            ->with('success', 'Grade deleted successfully');
    }
                
}
