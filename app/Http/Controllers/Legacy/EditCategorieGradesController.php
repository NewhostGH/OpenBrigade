<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EditCategorieGrades;
use Illuminate\Http\Request;

/**
 * Legacy migration source: edit_categorie_grades.php
 * Legacy pattern: generic
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class EditCategorieGradesController extends Controller
{
    public function index(Request $request)
    {
        $query = EditCategorieGrades::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('descriptionasterisktdtdalignleftinputtypetextclassformcontrolformcontrolsmnamedescription_catvaluesize30tdtdalignrighttrelseifupdcat1queryselectcg_code', 'like', '%' . $term . '%');
                $query->orWhere('cg_description', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.edit_categorie_grades.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.edit_categorie_grades.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EditCategorieGrades::findOrFail($id);

        return view('legacy_migrated.edit_categorie_grades.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = EditCategorieGrades::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.edit_categorie_grades.edit', $item->id)
            ->with('success', 'EditCategorieGrades created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EditCategorieGrades::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.edit_categorie_grades.edit', $item->id)
            ->with('success', 'EditCategorieGrades updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EditCategorieGrades::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.edit_categorie_grades.index')
            ->with('success', 'EditCategorieGrades deleted successfully');
    }
                

    public function applyOperation(Request $request)
    {
        $operation = (string) $request->input('operation');

        if ($operation === 'insert') {
            return response()->json(['status' => 'ok', 'operation' => 'insert']);
        }

        if ($operation === 'upd') {
            return response()->json(['status' => 'ok', 'operation' => 'upd']);
        }

        return response()->json(['status' => 'ignored', 'operation' => $operation]);
    }
}
