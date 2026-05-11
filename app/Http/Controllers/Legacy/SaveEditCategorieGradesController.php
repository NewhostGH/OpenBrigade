<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EditCategorieGrades;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_edit_categorie_grades.php
 * Legacy pattern: save
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class SaveEditCategorieGradesController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_edit_categorie_grades.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EditCategorieGrades::findOrFail($id);

        return view('legacy_migrated.save_edit_categorie_grades.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code_cat' => 'nullable|string|max:255',
            'description_cat' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
        ]);

        $item = EditCategorieGrades::create([
            'code_cat' => $validated['code_cat'] ?? null,
            'description_cat' => $validated['description_cat'] ?? null,
            'operation' => $validated['operation'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_edit_categorie_grades.edit', $item->id)
            ->with('success', 'EditCategorieGrades created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EditCategorieGrades::findOrFail($id);

        $validated = $request->validate([
            'code_cat' => 'nullable|string|max:255',
            'description_cat' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
        ]);

        $item->update([
            'code_cat' => $validated['code_cat'] ?? null,
            'description_cat' => $validated['description_cat'] ?? null,
            'operation' => $validated['operation'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_edit_categorie_grades.edit', $item->id)
            ->with('success', 'EditCategorieGrades updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EditCategorieGrades::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_edit_categorie_grades.index')
            ->with('success', 'EditCategorieGrades deleted successfully');
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
