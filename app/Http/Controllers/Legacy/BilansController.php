<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Bilans;
use Illuminate\Http\Request;

/**
 * Legacy migration source: bilans.php
 * Legacy pattern: generic
 * Legacy permission id: 27
 * This file stems from a legacy migration and requires functional verification.
 */
class BilansController extends Controller
{
    public function index(Request $request)
    {
        $query = Bilans::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.bilans.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.bilans.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Bilans::findOrFail($id);

        return view('legacy_migrated.bilans.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'filter' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
        ]);

        $item = Bilans::create([
            'filter' => $validated['filter'] ?? null,
            'year' => $validated['year'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.bilans.edit', $item->id)
            ->with('success', 'Bilans created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Bilans::findOrFail($id);

        $validated = $request->validate([
            'filter' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
        ]);

        $item->update([
            'filter' => $validated['filter'] ?? null,
            'year' => $validated['year'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.bilans.edit', $item->id)
            ->with('success', 'Bilans updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Bilans::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.bilans.index')
            ->with('success', 'Bilans deleted successfully');
    }
                
}
