<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\FonctionsBank;
use Illuminate\Http\Request;

/**
 * Legacy migration source: fonctions_bank.php
 * Legacy pattern: list
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class FonctionsBankController extends Controller
{
    public function index(Request $request)
    {
        $query = FonctionsBank::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('concatcode_banque', 'like', '%' . $term . '%');
                $query->orWhere('etablissement', 'like', '%' . $term . '%');
                $query->orWhere('guichet', 'like', '%' . $term . '%');
                $query->orWhere('compte', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.fonctions_bank.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.fonctions_bank.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = FonctionsBank::findOrFail($id);

        return view('legacy_migrated.fonctions_bank.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = FonctionsBank::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.fonctions_bank.edit', $item->id)
            ->with('success', 'FonctionsBank created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = FonctionsBank::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.fonctions_bank.edit', $item->id)
            ->with('success', 'FonctionsBank updated successfully');
    }
                

    public function destroy($id)
    {
        $item = FonctionsBank::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.fonctions_bank.index')
            ->with('success', 'FonctionsBank deleted successfully');
    }
                
}
