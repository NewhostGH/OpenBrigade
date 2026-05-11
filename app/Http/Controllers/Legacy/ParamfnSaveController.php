<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Paramfn;
use Illuminate\Http\Request;

/**
 * Legacy migration source: paramfn_save.php
 * Legacy pattern: save
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class ParamfnSaveController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.paramfn_save.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Paramfn::findOrFail($id);

        return view('legacy_migrated.paramfn_save.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Paramfn::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.paramfn_save.edit', $item->id)
            ->with('success', 'Paramfn created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Paramfn::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.paramfn_save.edit', $item->id)
            ->with('success', 'Paramfn updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Paramfn::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.paramfn_save.index')
            ->with('success', 'Paramfn deleted successfully');
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
