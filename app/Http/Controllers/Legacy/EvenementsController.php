<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Evenements;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenements.php
 * Legacy pattern: list
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementsController extends Controller
{
    public function index(Request $request)
    {
        $query = Evenements::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_id', 'like', '%' . $term . '%');
                $query->orWhere('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
                $query->orWhere('p_code', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.evenements.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.evenements.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Evenements::findOrFail($id);

        return view('legacy_migrated.evenements.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Evenements::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.evenements.edit', $item->id)
            ->with('success', 'Evenements created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Evenements::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.evenements.edit', $item->id)
            ->with('success', 'Evenements updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Evenements::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenements.index')
            ->with('success', 'Evenements deleted successfully');
    }
                
}
