<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\History;
use Illuminate\Http\Request;

/**
 * Legacy migration source: history.php
 * Legacy pattern: list
 * Legacy permission id: 49
 * This file stems from a legacy migration and requires functional verification.
 */
class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = History::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('lh_id', 'like', '%' . $term . '%');
                $query->orWhere('p_id', 'like', '%' . $term . '%');
                $query->orWhere('lh_stamp', 'like', '%' . $term . '%');
                $query->orWhere('dmykisdate', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.history.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.history.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = History::findOrFail($id);

        return view('legacy_migrated.history.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'filter' => 'nullable|string|max:255',
            'ltcode' => 'nullable|string|max:255',
        ]);

        $item = History::create([
            'filter' => $validated['filter'] ?? null,
            'ltcode' => $validated['ltcode'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.history.edit', $item->id)
            ->with('success', 'History created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = History::findOrFail($id);

        $validated = $request->validate([
            'filter' => 'nullable|string|max:255',
            'ltcode' => 'nullable|string|max:255',
        ]);

        $item->update([
            'filter' => $validated['filter'] ?? null,
            'ltcode' => $validated['ltcode'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.history.edit', $item->id)
            ->with('success', 'History updated successfully');
    }
                

    public function destroy($id)
    {
        $item = History::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.history.index')
            ->with('success', 'History deleted successfully');
    }
                
}
