<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Addons;
use Illuminate\Http\Request;

/**
 * Legacy migration source: addons.php
 * Legacy pattern: list
 * Legacy permission id: 78
 * This file stems from a legacy migration and requires functional verification.
 */
class AddonsController extends Controller
{
    public function index(Request $request)
    {
        $query = Addons::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_civilite', 'like', '%' . $term . '%');
                $query->orWhere('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
                $query->orWhere('p_section', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.addons.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.addons.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Addons::findOrFail($id);

        return view('legacy_migrated.addons.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tab' => 'nullable|string|max:255',
            'f$ID' => 'nullable|string|max:255',
            'f57' => 'nullable|string|max:255',
            'f60' => 'nullable|string|max:255',
            'gps_provider' => 'nullable|string|max:255',
            'api_key' => 'nullable|string|max:255',
        ]);

        $item = Addons::create([
            'tab' => $validated['tab'] ?? null,
            'f$ID' => $validated['f$ID'] ?? null,
            'f57' => $validated['f57'] ?? null,
            'f60' => $validated['f60'] ?? null,
            'gps_provider' => $validated['gps_provider'] ?? null,
            'api_key' => $validated['api_key'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.addons.edit', $item->id)
            ->with('success', 'Addons created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Addons::findOrFail($id);

        $validated = $request->validate([
            'tab' => 'nullable|string|max:255',
            'f$ID' => 'nullable|string|max:255',
            'f57' => 'nullable|string|max:255',
            'f60' => 'nullable|string|max:255',
            'gps_provider' => 'nullable|string|max:255',
            'api_key' => 'nullable|string|max:255',
        ]);

        $item->update([
            'tab' => $validated['tab'] ?? null,
            'f$ID' => $validated['f$ID'] ?? null,
            'f57' => $validated['f57'] ?? null,
            'f60' => $validated['f60'] ?? null,
            'gps_provider' => $validated['gps_provider'] ?? null,
            'api_key' => $validated['api_key'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.addons.edit', $item->id)
            ->with('success', 'Addons updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Addons::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.addons.index')
            ->with('success', 'Addons deleted successfully');
    }
                
}
