<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Calendar;
use Illuminate\Http\Request;

/**
 * Legacy migration source: calendar.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $query = Calendar::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('te_libelle', 'like', '%' . $term . '%');
                $query->orWhere('e_code', 'like', '%' . $term . '%');
                $query->orWhere('te_code', 'like', '%' . $term . '%');
                $query->orWhere('te_icon', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.calendar.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.calendar.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Calendar::findOrFail($id);

        return view('legacy_migrated.calendar.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'filter' => 'nullable|string|max:255',
        ]);

        $item = Calendar::create([
            'filter' => $validated['filter'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.calendar.edit', $item->id)
            ->with('success', 'Calendar created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Calendar::findOrFail($id);

        $validated = $request->validate([
            'filter' => 'nullable|string|max:255',
        ]);

        $item->update([
            'filter' => $validated['filter'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.calendar.edit', $item->id)
            ->with('success', 'Calendar updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Calendar::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.calendar.index')
            ->with('success', 'Calendar deleted successfully');
    }
                
}
