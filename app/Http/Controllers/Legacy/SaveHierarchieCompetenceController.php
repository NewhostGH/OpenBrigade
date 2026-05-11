<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\HierarchieCompetence;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_hierarchie_competence.php
 * Legacy pattern: save
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class SaveHierarchieCompetenceController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_hierarchie_competence.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = HierarchieCompetence::findOrFail($id);

        return view('legacy_migrated.save_hierarchie_competence.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = HierarchieCompetence::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.save_hierarchie_competence.edit', $item->id)
            ->with('success', 'HierarchieCompetence created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = HierarchieCompetence::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.save_hierarchie_competence.edit', $item->id)
            ->with('success', 'HierarchieCompetence updated successfully');
    }
                

    public function destroy($id)
    {
        $item = HierarchieCompetence::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_hierarchie_competence.index')
            ->with('success', 'HierarchieCompetence deleted successfully');
    }
                

    public function applyOperation(Request $request)
    {
        $operation = (string) $request->input('operation');

        if ($operation === 'delete') {
            return response()->json(['status' => 'ok', 'operation' => 'delete']);
        }

        if ($operation === 'delete_confirmed') {
            return response()->json(['status' => 'ok', 'operation' => 'delete_confirmed']);
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
