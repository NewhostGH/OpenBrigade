<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\ConfigurationTheme;
use Illuminate\Http\Request;

/**
 * Legacy migration source: configuration_theme.php
 * Legacy pattern: generic
 * Legacy permission id: 14
 * This file stems from a legacy migration and requires functional verification.
 */
class ConfigurationThemeController extends Controller
{
    public function index(Request $request)
    {
        $query = ConfigurationTheme::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('description', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.configuration_theme.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.configuration_theme.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = ConfigurationTheme::findOrFail($id);

        return view('legacy_migrated.configuration_theme.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'upload' => 'nullable|file',
        ]);

        $item = ConfigurationTheme::create([
            'image' => $validated['image'] ?? null,
            'action' => $validated['action'] ?? null,
            'upload' => $validated['upload'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.configuration_theme.edit', $item->id)
            ->with('success', 'ConfigurationTheme created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = ConfigurationTheme::findOrFail($id);

        $validated = $request->validate([
            'image' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'upload' => 'nullable|file',
        ]);

        $item->update([
            'image' => $validated['image'] ?? null,
            'action' => $validated['action'] ?? null,
            'upload' => $validated['upload'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.configuration_theme.edit', $item->id)
            ->with('success', 'ConfigurationTheme updated successfully');
    }
                

    public function destroy($id)
    {
        $item = ConfigurationTheme::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.configuration_theme.index')
            ->with('success', 'ConfigurationTheme deleted successfully');
    }
                
}
