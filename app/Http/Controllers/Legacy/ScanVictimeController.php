<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\ScanVictime;
use Illuminate\Http\Request;

/**
 * Legacy migration source: scan_victime.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class ScanVictimeController extends Controller
{
    public function index(Request $request)
    {
        $query = ScanVictime::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.scan_victime.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.scan_victime.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = ScanVictime::findOrFail($id);

        return view('legacy_migrated.scan_victime.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'webcameraChanger' => 'nullable|string|max:255',
        ]);

        $item = ScanVictime::create([
            'webcameraChanger' => $validated['webcameraChanger'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.scan_victime.edit', $item->id)
            ->with('success', 'ScanVictime created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = ScanVictime::findOrFail($id);

        $validated = $request->validate([
            'webcameraChanger' => 'nullable|string|max:255',
        ]);

        $item->update([
            'webcameraChanger' => $validated['webcameraChanger'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.scan_victime.edit', $item->id)
            ->with('success', 'ScanVictime updated successfully');
    }
                

    public function destroy($id)
    {
        $item = ScanVictime::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.scan_victime.index')
            ->with('success', 'ScanVictime deleted successfully');
    }
                
}
