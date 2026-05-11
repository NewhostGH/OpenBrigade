<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EvenementEquipes;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_equipes.php
 * Legacy pattern: list
 * Legacy permission id: 41
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementEquipesController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementEquipes::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('ee_icon', 'like', '%' . $term . '%');
                $query->orWhere('p_id', 'like', '%' . $term . '%');
                $query->orWhere('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.evenement_equipes.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.evenement_equipes.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EvenementEquipes::findOrFail($id);

        return view('legacy_migrated.evenement_equipes.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'EE_ORDER' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'equipe' => 'nullable|string|max:255',
            'EE_NAME' => 'nullable|string|max:255',
            'EE_DESCRIPTION' => 'nullable|string|max:255',
            'EE_ID_RADIO' => 'nullable|string|max:255',
            'EE_SIGNATURE' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
        ]);

        $item = EvenementEquipes::create([
            'EE_ORDER' => $validated['EE_ORDER'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'equipe' => $validated['equipe'] ?? null,
            'EE_NAME' => $validated['EE_NAME'] ?? null,
            'EE_DESCRIPTION' => $validated['EE_DESCRIPTION'] ?? null,
            'EE_ID_RADIO' => $validated['EE_ID_RADIO'] ?? null,
            'EE_SIGNATURE' => $validated['EE_SIGNATURE'] ?? null,
            'icon' => $validated['icon'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_equipes.edit', $item->id)
            ->with('success', 'EvenementEquipes created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EvenementEquipes::findOrFail($id);

        $validated = $request->validate([
            'EE_ORDER' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'equipe' => 'nullable|string|max:255',
            'EE_NAME' => 'nullable|string|max:255',
            'EE_DESCRIPTION' => 'nullable|string|max:255',
            'EE_ID_RADIO' => 'nullable|string|max:255',
            'EE_SIGNATURE' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
        ]);

        $item->update([
            'EE_ORDER' => $validated['EE_ORDER'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'equipe' => $validated['equipe'] ?? null,
            'EE_NAME' => $validated['EE_NAME'] ?? null,
            'EE_DESCRIPTION' => $validated['EE_DESCRIPTION'] ?? null,
            'EE_ID_RADIO' => $validated['EE_ID_RADIO'] ?? null,
            'EE_SIGNATURE' => $validated['EE_SIGNATURE'] ?? null,
            'icon' => $validated['icon'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_equipes.edit', $item->id)
            ->with('success', 'EvenementEquipes updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EvenementEquipes::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenement_equipes.index')
            ->with('success', 'EvenementEquipes deleted successfully');
    }
                
}
