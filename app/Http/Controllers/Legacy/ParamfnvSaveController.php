<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Paramfnv;
use Illuminate\Http\Request;

/**
 * Legacy migration source: paramfnv_save.php
 * Legacy pattern: save
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class ParamfnvSaveController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.paramfnv_save.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Paramfnv::findOrFail($id);

        return view('legacy_migrated.paramfnv_save.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Paramfnv::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.paramfnv_save.edit', $item->id)
            ->with('success', 'Paramfnv created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Paramfnv::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.paramfnv_save.edit', $item->id)
            ->with('success', 'Paramfnv updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Paramfnv::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.paramfnv_save.index')
            ->with('success', 'Paramfnv deleted successfully');
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
