<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Equipe;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_equipe.php
 * Legacy pattern: save
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class SaveEquipeController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_equipe.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Equipe::findOrFail($id);

        return view('legacy_migrated.save_equipe.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Equipe::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.save_equipe.edit', $item->id)
            ->with('success', 'Equipe created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Equipe::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.save_equipe.edit', $item->id)
            ->with('success', 'Equipe updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Equipe::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_equipe.index')
            ->with('success', 'Equipe deleted successfully');
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
