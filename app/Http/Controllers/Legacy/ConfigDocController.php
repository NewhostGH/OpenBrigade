<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\ConfigDoc;
use Illuminate\Http\Request;

/**
 * Legacy migration source: config_doc.php
 * Legacy pattern: generic
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class ConfigDocController extends Controller
{
    public function index(Request $request)
    {
        $query = ConfigDoc::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.config_doc.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.config_doc.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = ConfigDoc::findOrFail($id);

        return view('legacy_migrated.config_doc.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = ConfigDoc::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.config_doc.edit', $item->id)
            ->with('success', 'ConfigDoc created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = ConfigDoc::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.config_doc.edit', $item->id)
            ->with('success', 'ConfigDoc updated successfully');
    }
                

    public function destroy($id)
    {
        $item = ConfigDoc::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.config_doc.index')
            ->with('success', 'ConfigDoc deleted successfully');
    }
                
}
