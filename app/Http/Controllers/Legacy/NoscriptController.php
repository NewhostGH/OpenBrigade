<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Noscript;
use Illuminate\Http\Request;

/**
 * Legacy migration source: noscript.php
 * Legacy pattern: generic
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class NoscriptController extends Controller
{
    public function index(Request $request)
    {
        $query = Noscript::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.noscript.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.noscript.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Noscript::findOrFail($id);

        return view('legacy_migrated.noscript.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Noscript::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.noscript.edit', $item->id)
            ->with('success', 'Noscript created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Noscript::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.noscript.edit', $item->id)
            ->with('success', 'Noscript updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Noscript::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.noscript.index')
            ->with('success', 'Noscript deleted successfully');
    }
                
}
