<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\IndispoDisplay;
use Illuminate\Http\Request;

/**
 * Legacy migration source: indispo_display.php
 * Legacy pattern: list
 * Legacy permission id: 11
 * This file stems from a legacy migration and requires functional verification.
 */
class IndispoDisplayController extends Controller
{
    public function index(Request $request)
    {
        $query = IndispoDisplay::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('count1', 'like', '%' . $term . '%');
                $query->orWhere('p_id', 'like', '%' . $term . '%');
                $query->orWhere('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.indispo_display.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.indispo_display.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = IndispoDisplay::findOrFail($id);

        return view('legacy_migrated.indispo_display.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = IndispoDisplay::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.indispo_display.edit', $item->id)
            ->with('success', 'IndispoDisplay created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = IndispoDisplay::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.indispo_display.edit', $item->id)
            ->with('success', 'IndispoDisplay updated successfully');
    }
                

    public function destroy($id)
    {
        $item = IndispoDisplay::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.indispo_display.index')
            ->with('success', 'IndispoDisplay deleted successfully');
    }
                
}
