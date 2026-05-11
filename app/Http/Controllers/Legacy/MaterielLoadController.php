<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\MaterielLoad;
use Illuminate\Http\Request;

/**
 * Legacy migration source: materiel_load.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class MaterielLoadController extends Controller
{
    public function index(Request $request)
    {
        $query = MaterielLoad::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }
        if (session()->has('SES_COMPANY')) {
            $query->where('company_id', session('SES_COMPANY'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('tm_usage', 'like', '%' . $term . '%');
                $query->orWhere('ma_id', 'like', '%' . $term . '%');
                $query->orWhere('ma_modele', 'like', '%' . $term . '%');
                $query->orWhere('tm_code', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.materiel_load.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.materiel_load.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = MaterielLoad::findOrFail($id);

        return view('legacy_migrated.materiel_load.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = MaterielLoad::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.materiel_load.edit', $item->id)
            ->with('success', 'MaterielLoad created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = MaterielLoad::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.materiel_load.edit', $item->id)
            ->with('success', 'MaterielLoad updated successfully');
    }
                

    public function destroy($id)
    {
        $item = MaterielLoad::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.materiel_load.index')
            ->with('success', 'MaterielLoad deleted successfully');
    }
                
}
