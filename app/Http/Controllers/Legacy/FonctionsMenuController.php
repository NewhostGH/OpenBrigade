<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\FonctionsMenu;
use Illuminate\Http\Request;

/**
 * Legacy migration source: fonctions_menu.php
 * Legacy pattern: list
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class FonctionsMenuController extends Controller
{
    public function index(Request $request)
    {
        $query = FonctionsMenu::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }
        if (session()->has('SES_COMPANY')) {
            $query->where('company_id', session('SES_COMPANY'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('e_libelle', 'like', '%' . $term . '%');
                $query->orWhere('e_lieu', 'like', '%' . $term . '%');
                $query->orWhere('mg_code', 'like', '%' . $term . '%');
                $query->orWhere('mg_title', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.fonctions_menu.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.fonctions_menu.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = FonctionsMenu::findOrFail($id);

        return view('legacy_migrated.fonctions_menu.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = FonctionsMenu::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.fonctions_menu.edit', $item->id)
            ->with('success', 'FonctionsMenu created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = FonctionsMenu::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.fonctions_menu.edit', $item->id)
            ->with('success', 'FonctionsMenu updated successfully');
    }
                

    public function destroy($id)
    {
        $item = FonctionsMenu::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.fonctions_menu.index')
            ->with('success', 'FonctionsMenu deleted successfully');
    }
                
}
