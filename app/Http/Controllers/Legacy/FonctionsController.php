<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Fonctions;
use Illuminate\Http\Request;

/**
 * Legacy migration source: fonctions.php
 * Legacy pattern: list
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class FonctionsController extends Controller
{
    public function index(Request $request)
    {
        $query = Fonctions::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }
        if (session()->has('SES_COMPANY')) {
            $query->where('company_id', session('SES_COMPANY'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('pp_value', 'like', '%' . $term . '%');
                $query->orWhere('eq_id', 'like', '%' . $term . '%');
                $query->orWhere('eq_nom', 'like', '%' . $term . '%');
                $query->orWhere('ps_id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.fonctions.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.fonctions.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Fonctions::findOrFail($id);

        return view('legacy_migrated.fonctions.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'csrf_token_' => 'nullable|string|max:255',
            'element' => 'nullable|string|max:255',
        ]);

        $item = Fonctions::create([
            'csrf_token_' => $validated['csrf_token_'] ?? null,
            'element' => $validated['element'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.fonctions.edit', $item->id)
            ->with('success', 'Fonctions created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Fonctions::findOrFail($id);

        $validated = $request->validate([
            'csrf_token_' => 'nullable|string|max:255',
            'element' => 'nullable|string|max:255',
        ]);

        $item->update([
            'csrf_token_' => $validated['csrf_token_'] ?? null,
            'element' => $validated['element'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.fonctions.edit', $item->id)
            ->with('success', 'Fonctions updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Fonctions::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.fonctions.index')
            ->with('success', 'Fonctions deleted successfully');
    }
                
}
