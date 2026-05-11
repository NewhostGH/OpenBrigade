<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Grades;
use Illuminate\Http\Request;

/**
 * Legacy migration source: upd_grades.php
 * Legacy pattern: edit
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class UpdGradesController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.upd_grades.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Grades::findOrFail($id);

        return view('legacy_migrated.upd_grades.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'operation' => 'nullable|string|max:255',
            'OLD_G_GRADE' => 'nullable|string|max:255',
            'usage' => 'nullable|string|max:255',
            'categorie' => 'nullable|string|max:255',
            'G_GRADE' => 'nullable|string|max:255',
            'G_DESCRIPTION' => 'nullable|string|max:255',
            'G_LEVEL' => 'nullable|string|max:255',
            'G_TYPE' => 'nullable|string|max:255',
            'icone' => 'nullable|file',
            'annuler' => 'nullable|string|max:255',
        ]);

        $item = Grades::create([
            'operation' => $validated['operation'] ?? null,
            'OLD_G_GRADE' => $validated['OLD_G_GRADE'] ?? null,
            'usage' => $validated['usage'] ?? null,
            'categorie' => $validated['categorie'] ?? null,
            'G_GRADE' => $validated['G_GRADE'] ?? null,
            'G_DESCRIPTION' => $validated['G_DESCRIPTION'] ?? null,
            'G_LEVEL' => $validated['G_LEVEL'] ?? null,
            'G_TYPE' => $validated['G_TYPE'] ?? null,
            'icone' => $validated['icone'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_grades.edit', $item->id)
            ->with('success', 'Grades created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Grades::findOrFail($id);

        $validated = $request->validate([
            'operation' => 'nullable|string|max:255',
            'OLD_G_GRADE' => 'nullable|string|max:255',
            'usage' => 'nullable|string|max:255',
            'categorie' => 'nullable|string|max:255',
            'G_GRADE' => 'nullable|string|max:255',
            'G_DESCRIPTION' => 'nullable|string|max:255',
            'G_LEVEL' => 'nullable|string|max:255',
            'G_TYPE' => 'nullable|string|max:255',
            'icone' => 'nullable|file',
            'annuler' => 'nullable|string|max:255',
        ]);

        $item->update([
            'operation' => $validated['operation'] ?? null,
            'OLD_G_GRADE' => $validated['OLD_G_GRADE'] ?? null,
            'usage' => $validated['usage'] ?? null,
            'categorie' => $validated['categorie'] ?? null,
            'G_GRADE' => $validated['G_GRADE'] ?? null,
            'G_DESCRIPTION' => $validated['G_DESCRIPTION'] ?? null,
            'G_LEVEL' => $validated['G_LEVEL'] ?? null,
            'G_TYPE' => $validated['G_TYPE'] ?? null,
            'icone' => $validated['icone'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_grades.edit', $item->id)
            ->with('success', 'Grades updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Grades::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.upd_grades.index')
            ->with('success', 'Grades deleted successfully');
    }
                

    public function applyOperation(Request $request)
    {
        $operation = (string) $request->input('operation');

        if ($operation === 'insert') {
            return response()->json(['status' => 'ok', 'operation' => 'insert']);
        }

        if ($operation === 'update') {
            return response()->json(['status' => 'ok', 'operation' => 'update']);
        }

        return response()->json(['status' => 'ignored', 'operation' => $operation]);
    }
}
