<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EvenementFacturationDetail;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_facturation_detail.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementFacturationDetailController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementFacturationDetail::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('ef_lig', 'like', '%' . $term . '%');
                $query->orWhere('ef_frais', 'like', '%' . $term . '%');
                $query->orWhere('ef_txt', 'like', '%' . $term . '%');
                $query->orWhere('ef_qte', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.evenement_facturation_detail.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.evenement_facturation_detail.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EvenementFacturationDetail::findOrFail($id);

        return view('legacy_migrated.evenement_facturation_detail.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'submit' => 'nullable|string|max:255',
            'annuler' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'label' => 'nullable|string|max:255',
            'commentaire' => 'nullable|string|max:255',
            'quantite' => 'nullable|string|max:255',
            'pu' => 'nullable|string|max:255',
            'remise' => 'nullable|string|max:255',
            'subtotal' => 'nullable|string|max:255',
            'btcopie' => 'nullable|string|max:255',
            'retour' => 'nullable|string|max:255',
        ]);

        $item = EvenementFacturationDetail::create([
            'submit' => $validated['submit'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'type' => $validated['type'] ?? null,
            'label' => $validated['label'] ?? null,
            'commentaire' => $validated['commentaire'] ?? null,
            'quantite' => $validated['quantite'] ?? null,
            'pu' => $validated['pu'] ?? null,
            'remise' => $validated['remise'] ?? null,
            'subtotal' => $validated['subtotal'] ?? null,
            'btcopie' => $validated['btcopie'] ?? null,
            'retour' => $validated['retour'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_facturation_detail.edit', $item->id)
            ->with('success', 'EvenementFacturationDetail created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EvenementFacturationDetail::findOrFail($id);

        $validated = $request->validate([
            'submit' => 'nullable|string|max:255',
            'annuler' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'label' => 'nullable|string|max:255',
            'commentaire' => 'nullable|string|max:255',
            'quantite' => 'nullable|string|max:255',
            'pu' => 'nullable|string|max:255',
            'remise' => 'nullable|string|max:255',
            'subtotal' => 'nullable|string|max:255',
            'btcopie' => 'nullable|string|max:255',
            'retour' => 'nullable|string|max:255',
        ]);

        $item->update([
            'submit' => $validated['submit'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'type' => $validated['type'] ?? null,
            'label' => $validated['label'] ?? null,
            'commentaire' => $validated['commentaire'] ?? null,
            'quantite' => $validated['quantite'] ?? null,
            'pu' => $validated['pu'] ?? null,
            'remise' => $validated['remise'] ?? null,
            'subtotal' => $validated['subtotal'] ?? null,
            'btcopie' => $validated['btcopie'] ?? null,
            'retour' => $validated['retour'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_facturation_detail.edit', $item->id)
            ->with('success', 'EvenementFacturationDetail updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EvenementFacturationDetail::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenement_facturation_detail.index')
            ->with('success', 'EvenementFacturationDetail deleted successfully');
    }
                
}
