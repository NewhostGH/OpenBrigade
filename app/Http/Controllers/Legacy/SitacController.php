<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Sitac;
use Illuminate\Http\Request;

/**
 * Legacy migration source: sitac.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class SitacController extends Controller
{
    public function index(Request $request)
    {
        $query = Sitac::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('lat', 'like', '%' . $term . '%');
                $query->orWhere('lng', 'like', '%' . $term . '%');
                $query->orWhere('zoomlevel', 'like', '%' . $term . '%');
                $query->orWhere('maptypeid', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.sitac.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.sitac.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Sitac::findOrFail($id);

        return view('legacy_migrated.sitac.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ins' => 'nullable|string|max:255',
        ]);

        $item = Sitac::create([
            'ins' => $validated['ins'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.sitac.edit', $item->id)
            ->with('success', 'Sitac created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Sitac::findOrFail($id);

        $validated = $request->validate([
            'ins' => 'nullable|string|max:255',
        ]);

        $item->update([
            'ins' => $validated['ins'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.sitac.edit', $item->id)
            ->with('success', 'Sitac updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Sitac::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.sitac.index')
            ->with('success', 'Sitac deleted successfully');
    }
                
}
