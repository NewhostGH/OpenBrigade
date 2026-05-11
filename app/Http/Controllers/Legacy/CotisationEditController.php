<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\CotisationEdit;
use Illuminate\Http\Request;

/**
 * Legacy migration source: cotisation_edit.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class CotisationEditController extends Controller
{
    public function index(Request $request)
    {
        $query = CotisationEdit::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('valuephpechoscriptechoheadtraiterdeleteifisset_getrejet_idandactiondeleteverify_csrfcotisationqueryselectannee', 'like', '%' . $term . '%');
                $query->orWhere('montant_rejet', 'like', '%' . $term . '%');
                $query->orWhere('annee', 'like', '%' . $term . '%');
                $query->orWhere('montant', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.cotisation_edit.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.cotisation_edit.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = CotisationEdit::findOrFail($id);

        return view('legacy_migrated.cotisation_edit.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date_rejet' => 'nullable|string|max:255',
            'representer' => 'nullable|string|max:255',
            'regularise' => 'nullable|string|max:255',
            'date_regul' => 'nullable|string|max:255',
            'date_paiement' => 'nullable|string|max:255',
            'observation' => 'nullable|string|max:255',
            'commentaire' => 'nullable|string|max:255',
            'periode' => 'nullable|string|max:255',
            'defaut_bancaire' => 'nullable|string|max:255',
            'type_regularisation' => 'nullable|string|max:255',
            'type_paiement' => 'nullable|string|max:255',
            'compte_a_debiter' => 'nullable|string|max:255',
            'rejet_id' => 'nullable|string|max:255',
            'paiement_id' => 'nullable|string|max:255',
            'rembourse' => 'nullable|string|max:255',
            'pid' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'annee' => 'nullable|string|max:255',
            'montant_regul' => 'nullable|string|max:255',
        ]);

        $item = CotisationEdit::create([
            'date_rejet' => $validated['date_rejet'] ?? null,
            'representer' => $validated['representer'] ?? null,
            'regularise' => $validated['regularise'] ?? null,
            'date_regul' => $validated['date_regul'] ?? null,
            'date_paiement' => $validated['date_paiement'] ?? null,
            'observation' => $validated['observation'] ?? null,
            'commentaire' => $validated['commentaire'] ?? null,
            'periode' => $validated['periode'] ?? null,
            'defaut_bancaire' => $validated['defaut_bancaire'] ?? null,
            'type_regularisation' => $validated['type_regularisation'] ?? null,
            'type_paiement' => $validated['type_paiement'] ?? null,
            'compte_a_debiter' => $validated['compte_a_debiter'] ?? null,
            'rejet_id' => $validated['rejet_id'] ?? null,
            'paiement_id' => $validated['paiement_id'] ?? null,
            'rembourse' => $validated['rembourse'] ?? null,
            'pid' => $validated['pid'] ?? null,
            'note' => $validated['note'] ?? null,
            'action' => $validated['action'] ?? null,
            'annee' => $validated['annee'] ?? null,
            'montant_regul' => $validated['montant_regul'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.cotisation_edit.edit', $item->id)
            ->with('success', 'CotisationEdit created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = CotisationEdit::findOrFail($id);

        $validated = $request->validate([
            'date_rejet' => 'nullable|string|max:255',
            'representer' => 'nullable|string|max:255',
            'regularise' => 'nullable|string|max:255',
            'date_regul' => 'nullable|string|max:255',
            'date_paiement' => 'nullable|string|max:255',
            'observation' => 'nullable|string|max:255',
            'commentaire' => 'nullable|string|max:255',
            'periode' => 'nullable|string|max:255',
            'defaut_bancaire' => 'nullable|string|max:255',
            'type_regularisation' => 'nullable|string|max:255',
            'type_paiement' => 'nullable|string|max:255',
            'compte_a_debiter' => 'nullable|string|max:255',
            'rejet_id' => 'nullable|string|max:255',
            'paiement_id' => 'nullable|string|max:255',
            'rembourse' => 'nullable|string|max:255',
            'pid' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'annee' => 'nullable|string|max:255',
            'montant_regul' => 'nullable|string|max:255',
        ]);

        $item->update([
            'date_rejet' => $validated['date_rejet'] ?? null,
            'representer' => $validated['representer'] ?? null,
            'regularise' => $validated['regularise'] ?? null,
            'date_regul' => $validated['date_regul'] ?? null,
            'date_paiement' => $validated['date_paiement'] ?? null,
            'observation' => $validated['observation'] ?? null,
            'commentaire' => $validated['commentaire'] ?? null,
            'periode' => $validated['periode'] ?? null,
            'defaut_bancaire' => $validated['defaut_bancaire'] ?? null,
            'type_regularisation' => $validated['type_regularisation'] ?? null,
            'type_paiement' => $validated['type_paiement'] ?? null,
            'compte_a_debiter' => $validated['compte_a_debiter'] ?? null,
            'rejet_id' => $validated['rejet_id'] ?? null,
            'paiement_id' => $validated['paiement_id'] ?? null,
            'rembourse' => $validated['rembourse'] ?? null,
            'pid' => $validated['pid'] ?? null,
            'note' => $validated['note'] ?? null,
            'action' => $validated['action'] ?? null,
            'annee' => $validated['annee'] ?? null,
            'montant_regul' => $validated['montant_regul'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.cotisation_edit.edit', $item->id)
            ->with('success', 'CotisationEdit updated successfully');
    }
                

    public function destroy($id)
    {
        $item = CotisationEdit::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.cotisation_edit.index')
            ->with('success', 'CotisationEdit deleted successfully');
    }
                
}
