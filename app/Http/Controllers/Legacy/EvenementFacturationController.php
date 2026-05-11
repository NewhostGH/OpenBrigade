<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EvenementFacturation;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_facturation.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementFacturationController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementFacturation::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('te_libelle', 'like', '%' . $term . '%');
                $query->orWhere('te_icon', 'like', '%' . $term . '%');
                $query->orWhere('eh_date_debut', 'like', '%' . $term . '%');
                $query->orWhere('dmydtdb', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.evenement_facturation.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.evenement_facturation.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EvenementFacturation::findOrFail($id);

        return view('legacy_migrated.evenement_facturation.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'devisAccepte' => 'nullable|string|max:255',
            'devisDate' => 'nullable|string|max:255',
            'devisMontant' => 'nullable|string|max:255',
            'devisAcompte' => 'nullable|string|max:255',
            'devisNumero' => 'nullable|string|max:255',
            'devisLieu' => 'nullable|string|max:255',
            'devisOrga' => 'nullable|string|max:255',
            'devisCivilite' => 'nullable|string|max:255',
            'devisContact' => 'nullable|string|max:255',
            'devisCP' => 'nullable|string|max:255',
            'devisVille' => 'nullable|string|max:255',
            'devisTel1' => 'nullable|string|max:255',
            'devisTel2' => 'nullable|string|max:255',
            'devisFax' => 'nullable|string|max:255',
            'devisEmail' => 'nullable|string|max:255',
            'devisURL' => 'nullable|string|max:255',
            'factDate' => 'nullable|string|max:255',
            'factMontant' => 'nullable|string|max:255',
            'CopieDevis' => 'nullable|string|max:255',
            'factAcompte' => 'nullable|string|max:255',
        ]);

        $item = EvenementFacturation::create([
            'devisAccepte' => $validated['devisAccepte'] ?? null,
            'devisDate' => $validated['devisDate'] ?? null,
            'devisMontant' => $validated['devisMontant'] ?? null,
            'devisAcompte' => $validated['devisAcompte'] ?? null,
            'devisNumero' => $validated['devisNumero'] ?? null,
            'devisLieu' => $validated['devisLieu'] ?? null,
            'devisOrga' => $validated['devisOrga'] ?? null,
            'devisCivilite' => $validated['devisCivilite'] ?? null,
            'devisContact' => $validated['devisContact'] ?? null,
            'devisCP' => $validated['devisCP'] ?? null,
            'devisVille' => $validated['devisVille'] ?? null,
            'devisTel1' => $validated['devisTel1'] ?? null,
            'devisTel2' => $validated['devisTel2'] ?? null,
            'devisFax' => $validated['devisFax'] ?? null,
            'devisEmail' => $validated['devisEmail'] ?? null,
            'devisURL' => $validated['devisURL'] ?? null,
            'factDate' => $validated['factDate'] ?? null,
            'factMontant' => $validated['factMontant'] ?? null,
            'CopieDevis' => $validated['CopieDevis'] ?? null,
            'factAcompte' => $validated['factAcompte'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_facturation.edit', $item->id)
            ->with('success', 'EvenementFacturation created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EvenementFacturation::findOrFail($id);

        $validated = $request->validate([
            'devisAccepte' => 'nullable|string|max:255',
            'devisDate' => 'nullable|string|max:255',
            'devisMontant' => 'nullable|string|max:255',
            'devisAcompte' => 'nullable|string|max:255',
            'devisNumero' => 'nullable|string|max:255',
            'devisLieu' => 'nullable|string|max:255',
            'devisOrga' => 'nullable|string|max:255',
            'devisCivilite' => 'nullable|string|max:255',
            'devisContact' => 'nullable|string|max:255',
            'devisCP' => 'nullable|string|max:255',
            'devisVille' => 'nullable|string|max:255',
            'devisTel1' => 'nullable|string|max:255',
            'devisTel2' => 'nullable|string|max:255',
            'devisFax' => 'nullable|string|max:255',
            'devisEmail' => 'nullable|string|max:255',
            'devisURL' => 'nullable|string|max:255',
            'factDate' => 'nullable|string|max:255',
            'factMontant' => 'nullable|string|max:255',
            'CopieDevis' => 'nullable|string|max:255',
            'factAcompte' => 'nullable|string|max:255',
        ]);

        $item->update([
            'devisAccepte' => $validated['devisAccepte'] ?? null,
            'devisDate' => $validated['devisDate'] ?? null,
            'devisMontant' => $validated['devisMontant'] ?? null,
            'devisAcompte' => $validated['devisAcompte'] ?? null,
            'devisNumero' => $validated['devisNumero'] ?? null,
            'devisLieu' => $validated['devisLieu'] ?? null,
            'devisOrga' => $validated['devisOrga'] ?? null,
            'devisCivilite' => $validated['devisCivilite'] ?? null,
            'devisContact' => $validated['devisContact'] ?? null,
            'devisCP' => $validated['devisCP'] ?? null,
            'devisVille' => $validated['devisVille'] ?? null,
            'devisTel1' => $validated['devisTel1'] ?? null,
            'devisTel2' => $validated['devisTel2'] ?? null,
            'devisFax' => $validated['devisFax'] ?? null,
            'devisEmail' => $validated['devisEmail'] ?? null,
            'devisURL' => $validated['devisURL'] ?? null,
            'factDate' => $validated['factDate'] ?? null,
            'factMontant' => $validated['factMontant'] ?? null,
            'CopieDevis' => $validated['CopieDevis'] ?? null,
            'factAcompte' => $validated['factAcompte'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_facturation.edit', $item->id)
            ->with('success', 'EvenementFacturation updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EvenementFacturation::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenement_facturation.index')
            ->with('success', 'EvenementFacturation deleted successfully');
    }
                
}
