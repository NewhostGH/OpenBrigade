<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\FonctionsChart;
use Illuminate\Http\Request;

/**
 * Legacy migration source: fonctions_chart.php
 * Legacy pattern: list
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class FonctionsChartController extends Controller
{
    public function index(Request $request)
    {
        $query = FonctionsChart::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('functiongeneratedatavardatasetnforeachdataset', 'like', '%' . $term . '%');
                $query->orWhere('jsscriptbrowserfunctionrepo_connexion_heure_journeesection', 'like', '%' . $term . '%');
                $query->orWhere('subsectionsglobaldbc', 'like', '%' . $term . '%');
                $query->orWhere('a_debut', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.fonctions_chart.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.fonctions_chart.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = FonctionsChart::findOrFail($id);

        return view('legacy_migrated.fonctions_chart.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = FonctionsChart::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.fonctions_chart.edit', $item->id)
            ->with('success', 'FonctionsChart created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = FonctionsChart::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.fonctions_chart.edit', $item->id)
            ->with('success', 'FonctionsChart updated successfully');
    }
                

    public function destroy($id)
    {
        $item = FonctionsChart::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.fonctions_chart.index')
            ->with('success', 'FonctionsChart deleted successfully');
    }
                
}
