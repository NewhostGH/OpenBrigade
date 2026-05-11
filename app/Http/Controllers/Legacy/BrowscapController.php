<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Browscap;
use Illuminate\Http\Request;

/**
 * Legacy migration source: browscap.php
 * Legacy pattern: generic
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class BrowscapController extends Controller
{
    public function index(Request $request)
    {
        $query = Browscap::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.browscap.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.browscap.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Browscap::findOrFail($id);

        return view('legacy_migrated.browscap.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Browscap::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.browscap.edit', $item->id)
            ->with('success', 'Browscap created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Browscap::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.browscap.edit', $item->id)
            ->with('success', 'Browscap updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Browscap::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.browscap.index')
            ->with('success', 'Browscap deleted successfully');
    }
                
}
