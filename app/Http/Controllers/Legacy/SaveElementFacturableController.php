<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\ElementFacturable;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_element_facturable.php
 * Legacy pattern: save
 * Legacy permission id: 17
 * This file stems from a legacy migration and requires functional verification.
 */
class SaveElementFacturableController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_element_facturable.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = ElementFacturable::findOrFail($id);

        return view('legacy_migrated.save_element_facturable.form', [
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

        return redirect()->route('legacy_migrated.save_element_facturable.edit', $item->id)
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

        return redirect()->route('legacy_migrated.save_element_facturable.edit', $item->id)
            ->with('success', 'ElementFacturable updated successfully');
    }
                

    public function destroy($id)
    {
        $item = ElementFacturable::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_element_facturable.index')
            ->with('success', 'ElementFacturable deleted successfully');
    }
                

    public function applyOperation(Request $request)
    {
        $operation = (string) $request->input('operation');

        if ($operation === 'delete') {
            return response()->json(['status' => 'ok', 'operation' => 'delete']);
        }

        if ($operation === 'insert') {
            return response()->json(['status' => 'ok', 'operation' => 'insert']);
        }

        if ($operation === 'update') {
            return response()->json(['status' => 'ok', 'operation' => 'update']);
        }

        return response()->json(['status' => 'ignored', 'operation' => $operation]);
    }
}
