<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EditGrades;
use Illuminate\Http\Request;

/**
 * Legacy migration source: edit_grades.php
 * Legacy pattern: list
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class EditGradesController extends Controller
{
    public function index(Request $request)
    {
        $query = EditGrades::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('grade', 'like', '%' . $term . '%');
                $query->orWhere('catgorie', 'like', '%' . $term . '%');
                $query->orWhere('description', 'like', '%' . $term . '%');
                $query->orWhere('code', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.edit_grades.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.edit_grades.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EditGrades::findOrFail($id);

        return view('legacy_migrated.edit_grades.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'usage' => 'nullable|string|max:255',
            'activ' => 'nullable|string|max:255',
        ]);

        $item = EditGrades::create([
            'usage' => $validated['usage'] ?? null,
            'activ' => $validated['activ'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.edit_grades.edit', $item->id)
            ->with('success', 'EditGrades created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EditGrades::findOrFail($id);

        $validated = $request->validate([
            'usage' => 'nullable|string|max:255',
            'activ' => 'nullable|string|max:255',
        ]);

        $item->update([
            'usage' => $validated['usage'] ?? null,
            'activ' => $validated['activ'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.edit_grades.edit', $item->id)
            ->with('success', 'EditGrades updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EditGrades::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.edit_grades.index')
            ->with('success', 'EditGrades deleted successfully');
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
