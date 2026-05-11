<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Personnel;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_personnel.php
 * Legacy pattern: save
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class SavePersonnelController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_personnel.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Personnel::findOrFail($id);

        return view('legacy_migrated.save_personnel.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ignore_duplicate' => 'nullable|string|max:255',
            'P_ID' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'security' => 'nullable|string|max:255',
            'doc' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'suspendu' => 'nullable|string|max:255',
            'date_suspendu' => 'nullable|string|max:255',
            'date_fin_suspendu' => 'nullable|string|max:255',
            'npai' => 'nullable|string|max:255',
            'date_npai' => 'nullable|string|max:255',
            'debut' => 'nullable|string|max:255',
            'fin' => 'nullable|string|max:255',
            'licnum' => 'nullable|string|max:255',
            'licence_date' => 'nullable|string|max:255',
            'licence_end' => 'nullable|string|max:255',
            'id_api' => 'nullable|string|max:255',
            'birth' => 'nullable|string|max:255',
            'service' => 'nullable|string|max:255',
            'motif_radiation' => 'nullable|string|max:255',
        ]);

        $item = Personnel::create([
            'ignore_duplicate' => $validated['ignore_duplicate'] ?? null,
            'P_ID' => $validated['P_ID'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'security' => $validated['security'] ?? null,
            'doc' => $validated['doc'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'suspendu' => $validated['suspendu'] ?? null,
            'date_suspendu' => $validated['date_suspendu'] ?? null,
            'date_fin_suspendu' => $validated['date_fin_suspendu'] ?? null,
            'npai' => $validated['npai'] ?? null,
            'date_npai' => $validated['date_npai'] ?? null,
            'debut' => $validated['debut'] ?? null,
            'fin' => $validated['fin'] ?? null,
            'licnum' => $validated['licnum'] ?? null,
            'licence_date' => $validated['licence_date'] ?? null,
            'licence_end' => $validated['licence_end'] ?? null,
            'id_api' => $validated['id_api'] ?? null,
            'birth' => $validated['birth'] ?? null,
            'service' => $validated['service'] ?? null,
            'motif_radiation' => $validated['motif_radiation'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_personnel.edit', $item->id)
            ->with('success', 'Personnel created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Personnel::findOrFail($id);

        $validated = $request->validate([
            'ignore_duplicate' => 'nullable|string|max:255',
            'P_ID' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'security' => 'nullable|string|max:255',
            'doc' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'suspendu' => 'nullable|string|max:255',
            'date_suspendu' => 'nullable|string|max:255',
            'date_fin_suspendu' => 'nullable|string|max:255',
            'npai' => 'nullable|string|max:255',
            'date_npai' => 'nullable|string|max:255',
            'debut' => 'nullable|string|max:255',
            'fin' => 'nullable|string|max:255',
            'licnum' => 'nullable|string|max:255',
            'licence_date' => 'nullable|string|max:255',
            'licence_end' => 'nullable|string|max:255',
            'id_api' => 'nullable|string|max:255',
            'birth' => 'nullable|string|max:255',
            'service' => 'nullable|string|max:255',
            'motif_radiation' => 'nullable|string|max:255',
        ]);

        $item->update([
            'ignore_duplicate' => $validated['ignore_duplicate'] ?? null,
            'P_ID' => $validated['P_ID'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'security' => $validated['security'] ?? null,
            'doc' => $validated['doc'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'suspendu' => $validated['suspendu'] ?? null,
            'date_suspendu' => $validated['date_suspendu'] ?? null,
            'date_fin_suspendu' => $validated['date_fin_suspendu'] ?? null,
            'npai' => $validated['npai'] ?? null,
            'date_npai' => $validated['date_npai'] ?? null,
            'debut' => $validated['debut'] ?? null,
            'fin' => $validated['fin'] ?? null,
            'licnum' => $validated['licnum'] ?? null,
            'licence_date' => $validated['licence_date'] ?? null,
            'licence_end' => $validated['licence_end'] ?? null,
            'id_api' => $validated['id_api'] ?? null,
            'birth' => $validated['birth'] ?? null,
            'service' => $validated['service'] ?? null,
            'motif_radiation' => $validated['motif_radiation'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_personnel.edit', $item->id)
            ->with('success', 'Personnel updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Personnel::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_personnel.index')
            ->with('success', 'Personnel deleted successfully');
    }
                

    public function applyOperation(Request $request)
    {
        $operation = (string) $request->input('operation');

        if ($operation === 'document') {
            return response()->json(['status' => 'ok', 'operation' => 'document']);
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
