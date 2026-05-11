<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Config;
use Illuminate\Http\Request;

/**
 * Legacy migration source: config.php
 * Legacy pattern: generic
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class ConfigController extends Controller
{
    public function index(Request $request)
    {
        $query = Config::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.config.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.config.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Config::findOrFail($id);

        return view('legacy_migrated.config.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Config::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.config.edit', $item->id)
            ->with('success', 'Config created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Config::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.config.edit', $item->id)
            ->with('success', 'Config updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Config::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.config.index')
            ->with('success', 'Config deleted successfully');
    }
                
}
