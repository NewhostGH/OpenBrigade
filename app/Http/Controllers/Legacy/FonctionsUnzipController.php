<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\FonctionsUnzip;
use Illuminate\Http\Request;

/**
 * Legacy migration source: fonctions_unzip.php
 * Legacy pattern: generic
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class FonctionsUnzipController extends Controller
{
    public function index(Request $request)
    {
        $query = FonctionsUnzip::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.fonctions_unzip.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.fonctions_unzip.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = FonctionsUnzip::findOrFail($id);

        return view('legacy_migrated.fonctions_unzip.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = FonctionsUnzip::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.fonctions_unzip.edit', $item->id)
            ->with('success', 'FonctionsUnzip created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = FonctionsUnzip::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.fonctions_unzip.edit', $item->id)
            ->with('success', 'FonctionsUnzip updated successfully');
    }
                

    public function destroy($id)
    {
        $item = FonctionsUnzip::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.fonctions_unzip.index')
            ->with('success', 'FonctionsUnzip deleted successfully');
    }
                
}
