<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\FonctionsInfos;
use Illuminate\Http\Request;

/**
 * Legacy migration source: fonctions_infos.php
 * Legacy pattern: list
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class FonctionsInfosController extends Controller
{
    public function index(Request $request)
    {
        $query = FonctionsInfos::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('date_formatp_mdp_expiry', 'like', '%' . $term . '%');
                $query->orWhere('dmyp_mdp_expiry', 'like', '%' . $term . '%');
                $query->orWhere('datediffp_mdp_expiry', 'like', '%' . $term . '%');
                $query->orWhere('now', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.fonctions_infos.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.fonctions_infos.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = FonctionsInfos::findOrFail($id);

        return view('legacy_migrated.fonctions_infos.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = FonctionsInfos::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.fonctions_infos.edit', $item->id)
            ->with('success', 'FonctionsInfos created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = FonctionsInfos::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.fonctions_infos.edit', $item->id)
            ->with('success', 'FonctionsInfos updated successfully');
    }
                

    public function destroy($id)
    {
        $item = FonctionsInfos::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.fonctions_infos.index')
            ->with('success', 'FonctionsInfos deleted successfully');
    }
                
}
