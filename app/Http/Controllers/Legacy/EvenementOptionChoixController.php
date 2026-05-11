<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EvenementOptionChoix;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_option_choix.php
 * Legacy pattern: generic
 * Legacy permission id: 41
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementOptionChoixController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementOptionChoix::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
                $query->orWhere('s_code', 'like', '%' . $term . '%');
                $query->orWhere('s_id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.evenement_option_choix.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.evenement_option_choix.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EvenementOptionChoix::findOrFail($id);

        return view('legacy_migrated.evenement_option_choix.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'O' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'pid' => 'nullable|string|max:255',
            'inscription' => 'nullable|string|max:255',
            'insription' => 'nullable|string|max:255',
        ]);

        $item = EvenementOptionChoix::create([
            'O' => $validated['O'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'pid' => $validated['pid'] ?? null,
            'inscription' => $validated['inscription'] ?? null,
            'insription' => $validated['insription'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_option_choix.edit', $item->id)
            ->with('success', 'EvenementOptionChoix created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EvenementOptionChoix::findOrFail($id);

        $validated = $request->validate([
            'O' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'pid' => 'nullable|string|max:255',
            'inscription' => 'nullable|string|max:255',
            'insription' => 'nullable|string|max:255',
        ]);

        $item->update([
            'O' => $validated['O'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'pid' => $validated['pid'] ?? null,
            'inscription' => $validated['inscription'] ?? null,
            'insription' => $validated['insription'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_option_choix.edit', $item->id)
            ->with('success', 'EvenementOptionChoix updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EvenementOptionChoix::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenement_option_choix.index')
            ->with('success', 'EvenementOptionChoix deleted successfully');
    }
                
}
