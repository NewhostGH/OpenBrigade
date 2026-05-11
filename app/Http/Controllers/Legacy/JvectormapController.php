<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Jvectormap;
use Illuminate\Http\Request;

/**
 * Legacy migration source: jvectormap.php
 * Legacy pattern: generic
 * Legacy permission id: 27
 * This file stems from a legacy migration and requires functional verification.
 */
class JvectormapController extends Controller
{
    public function index(Request $request)
    {
        $query = Jvectormap::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('value', 'like', '%' . $term . '%');
                $query->orWhere('0foreachmaps', 'like', '%' . $term . '%');
                $query->orWhere('ps_id', 'like', '%' . $term . '%');
                $query->orWhere('description', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.jvectormap.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.jvectormap.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Jvectormap::findOrFail($id);

        return view('legacy_migrated.jvectormap.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'report' => 'nullable|string|max:255',
            'param' => 'nullable|string|max:255',
        ]);

        $item = Jvectormap::create([
            'report' => $validated['report'] ?? null,
            'param' => $validated['param'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.jvectormap.edit', $item->id)
            ->with('success', 'Jvectormap created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Jvectormap::findOrFail($id);

        $validated = $request->validate([
            'report' => 'nullable|string|max:255',
            'param' => 'nullable|string|max:255',
        ]);

        $item->update([
            'report' => $validated['report'] ?? null,
            'param' => $validated['param'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.jvectormap.edit', $item->id)
            ->with('success', 'Jvectormap updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Jvectormap::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.jvectormap.index')
            ->with('success', 'Jvectormap deleted successfully');
    }
                
}
