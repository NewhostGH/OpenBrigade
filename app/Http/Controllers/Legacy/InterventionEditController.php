<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\InterventionEdit;
use Illuminate\Http\Request;

/**
 * Legacy migration source: intervention_edit.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class InterventionEditController extends Controller
{
    public function index(Request $request)
    {
        $query = InterventionEdit::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('e_code', 'like', '%' . $term . '%');
                $query->orWhere('vi_id', 'like', '%' . $term . '%');
                $query->orWhere('e_libelle', 'like', '%' . $term . '%');
                $query->orWhere('s_code', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.intervention_edit.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.intervention_edit.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = InterventionEdit::findOrFail($id);

        return view('legacy_migrated.intervention_edit.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'important' => 'nullable|string|max:255',
            'date_debut' => 'nullable|string|max:255',
            'heure_debut' => 'nullable|string|max:255',
            'heure_sll' => 'nullable|string|max:255',
            'imprimer' => 'nullable|string|max:255',
            'date_fin' => 'nullable|string|max:255',
            'heure_fin' => 'nullable|string|max:255',
            'comptage' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'eq_' => 'nullable|string|max:255',
            'commentaire' => 'nullable|string|max:255',
            'responsable' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'numinter' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'modeinter' => 'nullable|string|max:255',
            'security' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'titre' => 'nullable|string|max:255',
            'origine' => 'nullable|string|max:255',
        ]);

        $item = InterventionEdit::create([
            'important' => $validated['important'] ?? null,
            'date_debut' => $validated['date_debut'] ?? null,
            'heure_debut' => $validated['heure_debut'] ?? null,
            'heure_sll' => $validated['heure_sll'] ?? null,
            'imprimer' => $validated['imprimer'] ?? null,
            'date_fin' => $validated['date_fin'] ?? null,
            'heure_fin' => $validated['heure_fin'] ?? null,
            'comptage' => $validated['comptage'] ?? null,
            'address' => $validated['address'] ?? null,
            'eq_' => $validated['eq_'] ?? null,
            'commentaire' => $validated['commentaire'] ?? null,
            'responsable' => $validated['responsable'] ?? null,
            'action' => $validated['action'] ?? null,
            'numinter' => $validated['numinter'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'modeinter' => $validated['modeinter'] ?? null,
            'security' => $validated['security'] ?? null,
            'type' => $validated['type'] ?? null,
            'titre' => $validated['titre'] ?? null,
            'origine' => $validated['origine'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.intervention_edit.edit', $item->id)
            ->with('success', 'InterventionEdit created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = InterventionEdit::findOrFail($id);

        $validated = $request->validate([
            'important' => 'nullable|string|max:255',
            'date_debut' => 'nullable|string|max:255',
            'heure_debut' => 'nullable|string|max:255',
            'heure_sll' => 'nullable|string|max:255',
            'imprimer' => 'nullable|string|max:255',
            'date_fin' => 'nullable|string|max:255',
            'heure_fin' => 'nullable|string|max:255',
            'comptage' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'eq_' => 'nullable|string|max:255',
            'commentaire' => 'nullable|string|max:255',
            'responsable' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'numinter' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'modeinter' => 'nullable|string|max:255',
            'security' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'titre' => 'nullable|string|max:255',
            'origine' => 'nullable|string|max:255',
        ]);

        $item->update([
            'important' => $validated['important'] ?? null,
            'date_debut' => $validated['date_debut'] ?? null,
            'heure_debut' => $validated['heure_debut'] ?? null,
            'heure_sll' => $validated['heure_sll'] ?? null,
            'imprimer' => $validated['imprimer'] ?? null,
            'date_fin' => $validated['date_fin'] ?? null,
            'heure_fin' => $validated['heure_fin'] ?? null,
            'comptage' => $validated['comptage'] ?? null,
            'address' => $validated['address'] ?? null,
            'eq_' => $validated['eq_'] ?? null,
            'commentaire' => $validated['commentaire'] ?? null,
            'responsable' => $validated['responsable'] ?? null,
            'action' => $validated['action'] ?? null,
            'numinter' => $validated['numinter'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'modeinter' => $validated['modeinter'] ?? null,
            'security' => $validated['security'] ?? null,
            'type' => $validated['type'] ?? null,
            'titre' => $validated['titre'] ?? null,
            'origine' => $validated['origine'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.intervention_edit.edit', $item->id)
            ->with('success', 'InterventionEdit updated successfully');
    }
                

    public function destroy($id)
    {
        $item = InterventionEdit::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.intervention_edit.index')
            ->with('success', 'InterventionEdit deleted successfully');
    }
                
}
