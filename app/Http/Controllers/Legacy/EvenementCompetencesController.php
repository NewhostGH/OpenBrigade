<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EvenementCompetences;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_competences.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementCompetencesController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementCompetences::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('te_code', 'like', '%' . $term . '%');
                $query->orWhere('e_libelle', 'like', '%' . $term . '%');
                $query->orWhere('e_closed', 'like', '%' . $term . '%');
                $query->orWhere('e_canceled', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.evenement_competences.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.evenement_competences.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EvenementCompetences::findOrFail($id);

        return view('legacy_migrated.evenement_competences.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'global' => 'nullable|string|max:255',
            'new_competence' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'garde' => 'nullable|string|max:255',
            'partie' => 'nullable|string|max:255',
            'new_nb' => 'nullable|string|max:255',
        ]);

        $item = EvenementCompetences::create([
            'global' => $validated['global'] ?? null,
            'new_competence' => $validated['new_competence'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'garde' => $validated['garde'] ?? null,
            'partie' => $validated['partie'] ?? null,
            'new_nb' => $validated['new_nb'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_competences.edit', $item->id)
            ->with('success', 'EvenementCompetences created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EvenementCompetences::findOrFail($id);

        $validated = $request->validate([
            'global' => 'nullable|string|max:255',
            'new_competence' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'garde' => 'nullable|string|max:255',
            'partie' => 'nullable|string|max:255',
            'new_nb' => 'nullable|string|max:255',
        ]);

        $item->update([
            'global' => $validated['global'] ?? null,
            'new_competence' => $validated['new_competence'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'garde' => $validated['garde'] ?? null,
            'partie' => $validated['partie'] ?? null,
            'new_nb' => $validated['new_nb'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_competences.edit', $item->id)
            ->with('success', 'EvenementCompetences updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EvenementCompetences::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenement_competences.index')
            ->with('success', 'EvenementCompetences deleted successfully');
    }
                
}
