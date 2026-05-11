<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EvenementInscription;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_inscription.php
 * Legacy pattern: list
 * Legacy permission id: 39
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementInscriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementInscription::query();

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

        return view('legacy_migrated.evenement_inscription.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.evenement_inscription.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EvenementInscription::findOrFail($id);

        return view('legacy_migrated.evenement_inscription.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'evenement' => 'nullable|string|max:255',
            'P_ID' => 'nullable|string|max:255',
            'accept' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'chien_' => 'nullable|string|max:255',
            'vehic_' => 'nullable|string|max:255',
            'statut' => 'nullable|string|max:255',
            'inscription[]' => 'nullable|string|max:255',
            'value' => 'nullable|string|max:255',
            'EP_FLAG1' => 'nullable|string|max:255',
            'inscription' => 'nullable|string|max:255',
        ]);

        $item = EvenementInscription::create([
            'evenement' => $validated['evenement'] ?? null,
            'P_ID' => $validated['P_ID'] ?? null,
            'accept' => $validated['accept'] ?? null,
            'action' => $validated['action'] ?? null,
            'chien_' => $validated['chien_'] ?? null,
            'vehic_' => $validated['vehic_'] ?? null,
            'statut' => $validated['statut'] ?? null,
            'inscription[]' => $validated['inscription[]'] ?? null,
            'value' => $validated['value'] ?? null,
            'EP_FLAG1' => $validated['EP_FLAG1'] ?? null,
            'inscription' => $validated['inscription'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_inscription.edit', $item->id)
            ->with('success', 'EvenementInscription created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EvenementInscription::findOrFail($id);

        $validated = $request->validate([
            'evenement' => 'nullable|string|max:255',
            'P_ID' => 'nullable|string|max:255',
            'accept' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'chien_' => 'nullable|string|max:255',
            'vehic_' => 'nullable|string|max:255',
            'statut' => 'nullable|string|max:255',
            'inscription[]' => 'nullable|string|max:255',
            'value' => 'nullable|string|max:255',
            'EP_FLAG1' => 'nullable|string|max:255',
            'inscription' => 'nullable|string|max:255',
        ]);

        $item->update([
            'evenement' => $validated['evenement'] ?? null,
            'P_ID' => $validated['P_ID'] ?? null,
            'accept' => $validated['accept'] ?? null,
            'action' => $validated['action'] ?? null,
            'chien_' => $validated['chien_'] ?? null,
            'vehic_' => $validated['vehic_'] ?? null,
            'statut' => $validated['statut'] ?? null,
            'inscription[]' => $validated['inscription[]'] ?? null,
            'value' => $validated['value'] ?? null,
            'EP_FLAG1' => $validated['EP_FLAG1'] ?? null,
            'inscription' => $validated['inscription'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_inscription.edit', $item->id)
            ->with('success', 'EvenementInscription updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EvenementInscription::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenement_inscription.index')
            ->with('success', 'EvenementInscription deleted successfully');
    }
                
}
