<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\ElementFacturable;
use Illuminate\Http\Request;

/**
 * Legacy migration source: del_element_facturable.php
 * Legacy pattern: delete
 * Legacy permission id: 29
 * This file stems from a legacy migration and requires functional verification.
 */
class DelElementFacturableController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.del_element_facturable.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = ElementFacturable::findOrFail($id);

        return view('legacy_migrated.del_element_facturable.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = ElementFacturable::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_element_facturable.edit', $item->id)
            ->with('success', 'ElementFacturable created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = ElementFacturable::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_element_facturable.edit', $item->id)
            ->with('success', 'ElementFacturable updated successfully');
    }
                

    public function destroy($id)
    {
        $item = ElementFacturable::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.del_element_facturable.index')
            ->with('success', 'ElementFacturable deleted successfully');
    }
                
}
