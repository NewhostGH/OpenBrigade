<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\About;
use Illuminate\Http\Request;

/**
 * Legacy migration source: about.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class AboutController extends Controller
{
    public function index(Request $request)
    {
        $query = About::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.about.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.about.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = About::findOrFail($id);

        return view('legacy_migrated.about.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = About::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.about.edit', $item->id)
            ->with('success', 'About created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = About::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.about.edit', $item->id)
            ->with('success', 'About updated successfully');
    }
                

    public function destroy($id)
    {
        $item = About::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.about.index')
            ->with('success', 'About deleted successfully');
    }
                
}
