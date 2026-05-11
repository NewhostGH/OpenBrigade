<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\InstallAddon;
use Illuminate\Http\Request;

/**
 * Legacy migration source: install_addon.php
 * Legacy pattern: generic
 * Legacy permission id: 14
 * This file stems from a legacy migration and requires functional verification.
 */
class InstallAddonController extends Controller
{
    public function index(Request $request)
    {
        $query = InstallAddon::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.install_addon.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.install_addon.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = InstallAddon::findOrFail($id);

        return view('legacy_migrated.install_addon.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'module' => 'nullable|string|max:255',
            'reason' => 'nullable|string|max:255',
            'version' => 'nullable|string|max:255',
            'licence' => 'nullable|string|max:255',
            'libelle' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'end_datetime' => 'nullable|string|max:255',
            'section_id' => 'nullable|string|max:255',
            'seats' => 'nullable|string|max:255',
        ]);

        $item = InstallAddon::create([
            'module' => $validated['module'] ?? null,
            'reason' => $validated['reason'] ?? null,
            'version' => $validated['version'] ?? null,
            'licence' => $validated['licence'] ?? null,
            'libelle' => $validated['libelle'] ?? null,
            'description' => $validated['description'] ?? null,
            'end_datetime' => $validated['end_datetime'] ?? null,
            'section_id' => $validated['section_id'] ?? null,
            'seats' => $validated['seats'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.install_addon.edit', $item->id)
            ->with('success', 'InstallAddon created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = InstallAddon::findOrFail($id);

        $validated = $request->validate([
            'module' => 'nullable|string|max:255',
            'reason' => 'nullable|string|max:255',
            'version' => 'nullable|string|max:255',
            'licence' => 'nullable|string|max:255',
            'libelle' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'end_datetime' => 'nullable|string|max:255',
            'section_id' => 'nullable|string|max:255',
            'seats' => 'nullable|string|max:255',
        ]);

        $item->update([
            'module' => $validated['module'] ?? null,
            'reason' => $validated['reason'] ?? null,
            'version' => $validated['version'] ?? null,
            'licence' => $validated['licence'] ?? null,
            'libelle' => $validated['libelle'] ?? null,
            'description' => $validated['description'] ?? null,
            'end_datetime' => $validated['end_datetime'] ?? null,
            'section_id' => $validated['section_id'] ?? null,
            'seats' => $validated['seats'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.install_addon.edit', $item->id)
            ->with('success', 'InstallAddon updated successfully');
    }
                

    public function destroy($id)
    {
        $item = InstallAddon::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.install_addon.index')
            ->with('success', 'InstallAddon deleted successfully');
    }
                
}
