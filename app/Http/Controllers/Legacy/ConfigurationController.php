<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Configuration;
use Illuminate\Http\Request;

/**
 * Legacy migration source: configuration.php
 * Legacy pattern: list
 * Legacy permission id: 14
 * This file stems from a legacy migration and requires functional verification.
 */
class ConfigurationController extends Controller
{
    public function index(Request $request)
    {
        $query = Configuration::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('name', 'like', '%' . $term . '%');
                $query->orWhere('classformcontrolformcontrolsmidf79namefidforeachtypes_org', 'like', '%' . $term . '%');
                $query->orWhere('logo', 'like', '%' . $term . '%');
                $query->orWhere('banner', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.configuration.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.configuration.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Configuration::findOrFail($id);

        return view('legacy_migrated.configuration.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'f$ID' => 'nullable|string|max:255',
            'tab' => 'nullable|string|max:255',
            'f76' => 'nullable|string|max:255',
            'f96' => 'nullable|string|max:255',
            'f97' => 'nullable|string|max:255',
            'f101' => 'nullable|string|max:255',
        ]);

        $item = Configuration::create([
            'f$ID' => $validated['f$ID'] ?? null,
            'tab' => $validated['tab'] ?? null,
            'f76' => $validated['f76'] ?? null,
            'f96' => $validated['f96'] ?? null,
            'f97' => $validated['f97'] ?? null,
            'f101' => $validated['f101'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.configuration.edit', $item->id)
            ->with('success', 'Configuration created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Configuration::findOrFail($id);

        $validated = $request->validate([
            'f$ID' => 'nullable|string|max:255',
            'tab' => 'nullable|string|max:255',
            'f76' => 'nullable|string|max:255',
            'f96' => 'nullable|string|max:255',
            'f97' => 'nullable|string|max:255',
            'f101' => 'nullable|string|max:255',
        ]);

        $item->update([
            'f$ID' => $validated['f$ID'] ?? null,
            'tab' => $validated['tab'] ?? null,
            'f76' => $validated['f76'] ?? null,
            'f96' => $validated['f96'] ?? null,
            'f97' => $validated['f97'] ?? null,
            'f101' => $validated['f101'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.configuration.edit', $item->id)
            ->with('success', 'Configuration updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Configuration::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.configuration.index')
            ->with('success', 'Configuration deleted successfully');
    }
                
}
