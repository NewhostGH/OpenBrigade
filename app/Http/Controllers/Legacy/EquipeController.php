<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Equipe;
use Illuminate\Http\Request;

/**
 * Legacy migration source: equipe.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class EquipeController extends Controller
{
    public function index(Request $request)
    {
        $query = Equipe::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('eq_id', 'like', '%' . $term . '%');
                $query->orWhere('eq_nom', 'like', '%' . $term . '%');
                $query->orWhere('eq_order', 'like', '%' . $term . '%');
                $query->orWhere('count1', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.equipe.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.equipe.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Equipe::findOrFail($id);

        return view('legacy_migrated.equipe.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Equipe::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.equipe.edit', $item->id)
            ->with('success', 'Equipe created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Equipe::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.equipe.edit', $item->id)
            ->with('success', 'Equipe updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Equipe::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.equipe.index')
            ->with('success', 'Equipe deleted successfully');
    }
                
}
