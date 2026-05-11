<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\ConfigPrelevements;
use Illuminate\Http\Request;

/**
 * Legacy migration source: config_prelevements.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class ConfigPrelevementsController extends Controller
{
    public function index(Request $request)
    {
        $query = ConfigPrelevements::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.config_prelevements.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.config_prelevements.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = ConfigPrelevements::findOrFail($id);

        return view('legacy_migrated.config_prelevements.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = ConfigPrelevements::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.config_prelevements.edit', $item->id)
            ->with('success', 'ConfigPrelevements created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = ConfigPrelevements::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.config_prelevements.edit', $item->id)
            ->with('success', 'ConfigPrelevements updated successfully');
    }
                

    public function destroy($id)
    {
        $item = ConfigPrelevements::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.config_prelevements.index')
            ->with('success', 'ConfigPrelevements deleted successfully');
    }
                
}
