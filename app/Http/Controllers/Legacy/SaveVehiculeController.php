<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Vehicule;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_vehicule.php
 * Legacy pattern: save
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class SaveVehiculeController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_vehicule.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Vehicule::findOrFail($id);

        return view('legacy_migrated.save_vehicule.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'section' => 'nullable|string|max:255',
            'vehicule' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'security' => 'nullable|string|max:255',
        ]);

        $item = Vehicule::create([
            'section' => $validated['section'] ?? null,
            'vehicule' => $validated['vehicule'] ?? null,
            'type' => $validated['type'] ?? null,
            'security' => $validated['security'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_vehicule.edit', $item->id)
            ->with('success', 'Vehicule created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Vehicule::findOrFail($id);

        $validated = $request->validate([
            'section' => 'nullable|string|max:255',
            'vehicule' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'security' => 'nullable|string|max:255',
        ]);

        $item->update([
            'section' => $validated['section'] ?? null,
            'vehicule' => $validated['vehicule'] ?? null,
            'type' => $validated['type'] ?? null,
            'security' => $validated['security'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_vehicule.edit', $item->id)
            ->with('success', 'Vehicule updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Vehicule::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_vehicule.index')
            ->with('success', 'Vehicule deleted successfully');
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
