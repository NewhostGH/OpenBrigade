<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\ImportApi;
use Illuminate\Http\Request;

/**
 * Legacy migration source: import_api.php
 * Legacy pattern: generic
 * Legacy permission id: 14
 * This file stems from a legacy migration and requires functional verification.
 */
class ImportApiController extends Controller
{
    public function index(Request $request)
    {
        $query = ImportApi::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.import_api.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.import_api.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = ImportApi::findOrFail($id);

        return view('legacy_migrated.import_api.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'start' => 'nullable|string|max:255',
            'pid' => 'nullable|string|max:255',
            'importer' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:255',
        ]);

        $item = ImportApi::create([
            'start' => $validated['start'] ?? null,
            'pid' => $validated['pid'] ?? null,
            'importer' => $validated['importer'] ?? null,
            'number' => $validated['number'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.import_api.edit', $item->id)
            ->with('success', 'ImportApi created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = ImportApi::findOrFail($id);

        $validated = $request->validate([
            'start' => 'nullable|string|max:255',
            'pid' => 'nullable|string|max:255',
            'importer' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:255',
        ]);

        $item->update([
            'start' => $validated['start'] ?? null,
            'pid' => $validated['pid'] ?? null,
            'importer' => $validated['importer'] ?? null,
            'number' => $validated['number'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.import_api.edit', $item->id)
            ->with('success', 'ImportApi updated successfully');
    }
                

    public function destroy($id)
    {
        $item = ImportApi::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.import_api.index')
            ->with('success', 'ImportApi deleted successfully');
    }
                
}
