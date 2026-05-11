<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\TypeMateriel;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_type_materiel.php
 * Legacy pattern: save
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class SaveTypeMaterielController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_type_materiel.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = TypeMateriel::findOrFail($id);

        return view('legacy_migrated.save_type_materiel.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = TypeMateriel::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.save_type_materiel.edit', $item->id)
            ->with('success', 'TypeMateriel created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = TypeMateriel::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.save_type_materiel.edit', $item->id)
            ->with('success', 'TypeMateriel updated successfully');
    }
                

    public function destroy($id)
    {
        $item = TypeMateriel::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_type_materiel.index')
            ->with('success', 'TypeMateriel deleted successfully');
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
