<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\SitacOptions;
use Illuminate\Http\Request;

/**
 * Legacy migration source: sitac_options.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class SitacOptionsController extends Controller
{
    public function index(Request $request)
    {
        $query = SitacOptions::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('count1', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.sitac_options.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.sitac_options.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = SitacOptions::findOrFail($id);

        return view('legacy_migrated.sitac_options.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'autorefresh_select' => 'nullable|string|max:255',
        ]);

        $item = SitacOptions::create([
            'autorefresh_select' => $validated['autorefresh_select'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.sitac_options.edit', $item->id)
            ->with('success', 'SitacOptions created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = SitacOptions::findOrFail($id);

        $validated = $request->validate([
            'autorefresh_select' => 'nullable|string|max:255',
        ]);

        $item->update([
            'autorefresh_select' => $validated['autorefresh_select'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.sitac_options.edit', $item->id)
            ->with('success', 'SitacOptions updated successfully');
    }
                

    public function destroy($id)
    {
        $item = SitacOptions::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.sitac_options.index')
            ->with('success', 'SitacOptions deleted successfully');
    }
                
}
