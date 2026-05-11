<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\CategorieGrade;
use Illuminate\Http\Request;

/**
 * Legacy migration source: del_categorie_grade.php
 * Legacy pattern: delete
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class DelCategorieGradeController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.del_categorie_grade.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = CategorieGrade::findOrFail($id);

        return view('legacy_migrated.del_categorie_grade.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = CategorieGrade::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_categorie_grade.edit', $item->id)
            ->with('success', 'CategorieGrade created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = CategorieGrade::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_categorie_grade.edit', $item->id)
            ->with('success', 'CategorieGrade updated successfully');
    }
                

    public function destroy($id)
    {
        $item = CategorieGrade::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.del_categorie_grade.index')
            ->with('success', 'CategorieGrade deleted successfully');
    }
                
}
