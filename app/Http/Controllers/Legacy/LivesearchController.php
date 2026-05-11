<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Livesearch;
use Illuminate\Http\Request;

/**
 * Legacy migration source: livesearch.php
 * Legacy pattern: list
 * Legacy permission id: 40
 * This file stems from a legacy migration and requires functional verification.
 */
class LivesearchController extends Controller
{
    public function index(Request $request)
    {
        $query = Livesearch::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('distinctp_id', 'like', '%' . $term . '%');
                $query->orWhere('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
                $query->orWhere('p_old_member', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.livesearch.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.livesearch.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Livesearch::findOrFail($id);

        return view('legacy_migrated.livesearch.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Livesearch::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.livesearch.edit', $item->id)
            ->with('success', 'Livesearch created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Livesearch::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.livesearch.edit', $item->id)
            ->with('success', 'Livesearch updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Livesearch::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.livesearch.index')
            ->with('success', 'Livesearch deleted successfully');
    }
                
}
