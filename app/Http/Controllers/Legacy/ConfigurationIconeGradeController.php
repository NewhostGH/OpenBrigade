<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\ConfigurationIconeGrade;
use Illuminate\Http\Request;

/**
 * Legacy migration source: configuration_icone_grade.php
 * Legacy pattern: generic
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class ConfigurationIconeGradeController extends Controller
{
    public function index(Request $request)
    {
        $query = ConfigurationIconeGrade::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('g_description', 'like', '%' . $term . '%');
                $query->orWhere('g_icon', 'like', '%' . $term . '%');
                $query->orWhere('g_category', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.configuration_icone_grade.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.configuration_icone_grade.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = ConfigurationIconeGrade::findOrFail($id);

        return view('legacy_migrated.configuration_icone_grade.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'preUpload' => 'nullable|file',
            'upload' => 'nullable|file',
        ]);

        $item = ConfigurationIconeGrade::create([
            'image' => $validated['image'] ?? null,
            'action' => $validated['action'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'preUpload' => $validated['preUpload'] ?? null,
            'upload' => $validated['upload'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.configuration_icone_grade.edit', $item->id)
            ->with('success', 'ConfigurationIconeGrade created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = ConfigurationIconeGrade::findOrFail($id);

        $validated = $request->validate([
            'image' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'preUpload' => 'nullable|file',
            'upload' => 'nullable|file',
        ]);

        $item->update([
            'image' => $validated['image'] ?? null,
            'action' => $validated['action'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'preUpload' => $validated['preUpload'] ?? null,
            'upload' => $validated['upload'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.configuration_icone_grade.edit', $item->id)
            ->with('success', 'ConfigurationIconeGrade updated successfully');
    }
                

    public function destroy($id)
    {
        $item = ConfigurationIconeGrade::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.configuration_icone_grade.index')
            ->with('success', 'ConfigurationIconeGrade deleted successfully');
    }
                
}
