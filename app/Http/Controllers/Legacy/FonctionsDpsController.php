<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\FonctionsDps;
use Illuminate\Http\Request;

/**
 * Legacy migration source: fonctions_dps.php
 * Legacy pattern: list
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class FonctionsDpsController extends Controller
{
    public function index(Request $request)
    {
        $query = FonctionsDps::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('dimp1', 'like', '%' . $term . '%');
                $query->orWhere('dimp2', 'like', '%' . $term . '%');
                $query->orWhere('dime1', 'like', '%' . $term . '%');
                $query->orWhere('dime2', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.fonctions_dps.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.fonctions_dps.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = FonctionsDps::findOrFail($id);

        return view('legacy_migrated.fonctions_dps.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = FonctionsDps::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.fonctions_dps.edit', $item->id)
            ->with('success', 'FonctionsDps created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = FonctionsDps::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.fonctions_dps.edit', $item->id)
            ->with('success', 'FonctionsDps updated successfully');
    }
                

    public function destroy($id)
    {
        $item = FonctionsDps::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.fonctions_dps.index')
            ->with('success', 'FonctionsDps deleted successfully');
    }
                
}
