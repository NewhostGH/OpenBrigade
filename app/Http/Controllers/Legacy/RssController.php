<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Rss;
use Illuminate\Http\Request;

/**
 * Legacy migration source: rss.php
 * Legacy pattern: list
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class RssController extends Controller
{
    public function index(Request $request)
    {
        $query = Rss::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('e_code', 'like', '%' . $term . '%');
                $query->orWhere('te_libelle', 'like', '%' . $term . '%');
                $query->orWhere('concatevenement', 'like', '%' . $term . '%');
                $query->orWhere('e_codersslink', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.rss.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.rss.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Rss::findOrFail($id);

        return view('legacy_migrated.rss.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Rss::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.rss.edit', $item->id)
            ->with('success', 'Rss created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Rss::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.rss.edit', $item->id)
            ->with('success', 'Rss updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Rss::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.rss.index')
            ->with('success', 'Rss deleted successfully');
    }
                
}
