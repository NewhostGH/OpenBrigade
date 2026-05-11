<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\FonctionsSql;
use Illuminate\Http\Request;

/**
 * Legacy migration source: fonctions_sql.php
 * Legacy pattern: list
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class FonctionsSqlController extends Controller
{
    public function index(Request $request)
    {
        $query = FonctionsSql::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('count1', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.fonctions_sql.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.fonctions_sql.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = FonctionsSql::findOrFail($id);

        return view('legacy_migrated.fonctions_sql.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = FonctionsSql::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.fonctions_sql.edit', $item->id)
            ->with('success', 'FonctionsSql created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = FonctionsSql::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.fonctions_sql.edit', $item->id)
            ->with('success', 'FonctionsSql updated successfully');
    }
                

    public function destroy($id)
    {
        $item = FonctionsSql::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.fonctions_sql.index')
            ->with('success', 'FonctionsSql deleted successfully');
    }
                
}
