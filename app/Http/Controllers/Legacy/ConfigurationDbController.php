<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\ConfigurationDb;
use Illuminate\Http\Request;

/**
 * Legacy migration source: configuration_db.php
 * Legacy pattern: generic
 * Legacy permission id: 14
 * This file stems from a legacy migration and requires functional verification.
 */
class ConfigurationDbController extends Controller
{
    public function index(Request $request)
    {
        $query = ConfigurationDb::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.configuration_db.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.configuration_db.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = ConfigurationDb::findOrFail($id);

        return view('legacy_migrated.configuration_db.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'save' => 'nullable|string|max:255',
            'server' => 'nullable|string|max:255',
            'user' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'database' => 'nullable|string|max:255',
        ]);

        $item = ConfigurationDb::create([
            'save' => $validated['save'] ?? null,
            'server' => $validated['server'] ?? null,
            'user' => $validated['user'] ?? null,
            'password' => $validated['password'] ?? null,
            'database' => $validated['database'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.configuration_db.edit', $item->id)
            ->with('success', 'ConfigurationDb created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = ConfigurationDb::findOrFail($id);

        $validated = $request->validate([
            'save' => 'nullable|string|max:255',
            'server' => 'nullable|string|max:255',
            'user' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'database' => 'nullable|string|max:255',
        ]);

        $item->update([
            'save' => $validated['save'] ?? null,
            'server' => $validated['server'] ?? null,
            'user' => $validated['user'] ?? null,
            'password' => $validated['password'] ?? null,
            'database' => $validated['database'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.configuration_db.edit', $item->id)
            ->with('success', 'ConfigurationDb updated successfully');
    }
                

    public function destroy($id)
    {
        $item = ConfigurationDb::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.configuration_db.index')
            ->with('success', 'ConfigurationDb deleted successfully');
    }
                
}
