<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EvenementEdit;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_edit.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementEditController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementEdit::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('te_code', 'like', '%' . $term . '%');
                $query->orWhere('colonne_renfort', 'like', '%' . $term . '%');
                $query->orWhere('e_code', 'like', '%' . $term . '%');
                $query->orWhere('e_libelle', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.evenement_edit.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.evenement_edit.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EvenementEdit::findOrFail($id);

        return view('legacy_migrated.evenement_edit.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mail1' => 'nullable|string|max:255',
            'mail2' => 'nullable|string|max:255',
            'mail3' => 'nullable|string|max:255',
            'parent' => 'nullable|string|max:255',
            'allow_reinforcement' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'copydetails' => 'nullable|string|max:255',
            'agreed' => 'nullable|string|max:255',
            'renforts' => 'nullable|string|max:255',
            'vehicules' => 'nullable|string|max:255',
            'materiel' => 'nullable|string|max:255',
            'personnel' => 'nullable|string|max:255',
            'options' => 'nullable|string|max:255',
            'copydetailsfrom' => 'nullable|string|max:255',
            'copycheffrom' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'type_garde' => 'nullable|string|max:255',
            'libelle' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        $item = EvenementEdit::create([
            'mail1' => $validated['mail1'] ?? null,
            'mail2' => $validated['mail2'] ?? null,
            'mail3' => $validated['mail3'] ?? null,
            'parent' => $validated['parent'] ?? null,
            'allow_reinforcement' => $validated['allow_reinforcement'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'action' => $validated['action'] ?? null,
            'copydetails' => $validated['copydetails'] ?? null,
            'agreed' => $validated['agreed'] ?? null,
            'renforts' => $validated['renforts'] ?? null,
            'vehicules' => $validated['vehicules'] ?? null,
            'materiel' => $validated['materiel'] ?? null,
            'personnel' => $validated['personnel'] ?? null,
            'options' => $validated['options'] ?? null,
            'copydetailsfrom' => $validated['copydetailsfrom'] ?? null,
            'copycheffrom' => $validated['copycheffrom'] ?? null,
            'type' => $validated['type'] ?? null,
            'type_garde' => $validated['type_garde'] ?? null,
            'libelle' => $validated['libelle'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_edit.edit', $item->id)
            ->with('success', 'EvenementEdit created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EvenementEdit::findOrFail($id);

        $validated = $request->validate([
            'mail1' => 'nullable|string|max:255',
            'mail2' => 'nullable|string|max:255',
            'mail3' => 'nullable|string|max:255',
            'parent' => 'nullable|string|max:255',
            'allow_reinforcement' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'copydetails' => 'nullable|string|max:255',
            'agreed' => 'nullable|string|max:255',
            'renforts' => 'nullable|string|max:255',
            'vehicules' => 'nullable|string|max:255',
            'materiel' => 'nullable|string|max:255',
            'personnel' => 'nullable|string|max:255',
            'options' => 'nullable|string|max:255',
            'copydetailsfrom' => 'nullable|string|max:255',
            'copycheffrom' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'type_garde' => 'nullable|string|max:255',
            'libelle' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        $item->update([
            'mail1' => $validated['mail1'] ?? null,
            'mail2' => $validated['mail2'] ?? null,
            'mail3' => $validated['mail3'] ?? null,
            'parent' => $validated['parent'] ?? null,
            'allow_reinforcement' => $validated['allow_reinforcement'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'action' => $validated['action'] ?? null,
            'copydetails' => $validated['copydetails'] ?? null,
            'agreed' => $validated['agreed'] ?? null,
            'renforts' => $validated['renforts'] ?? null,
            'vehicules' => $validated['vehicules'] ?? null,
            'materiel' => $validated['materiel'] ?? null,
            'personnel' => $validated['personnel'] ?? null,
            'options' => $validated['options'] ?? null,
            'copydetailsfrom' => $validated['copydetailsfrom'] ?? null,
            'copycheffrom' => $validated['copycheffrom'] ?? null,
            'type' => $validated['type'] ?? null,
            'type_garde' => $validated['type_garde'] ?? null,
            'libelle' => $validated['libelle'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_edit.edit', $item->id)
            ->with('success', 'EvenementEdit updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EvenementEdit::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenement_edit.index')
            ->with('success', 'EvenementEdit deleted successfully');
    }
                
}
