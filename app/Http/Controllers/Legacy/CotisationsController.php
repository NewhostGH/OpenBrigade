<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Cotisations;
use Illuminate\Http\Request;

/**
 * Legacy migration source: cotisations.php
 * Legacy pattern: list
 * Legacy permission id: 53
 * This file stems from a legacy migration and requires functional verification.
 */
class CotisationsController extends Controller
{
    public function index(Request $request)
    {
        $query = Cotisations::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('count', 'like', '%' . $term . '%');
                $query->orWhere('periode_code', 'like', '%' . $term . '%');
                $query->orWhere('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.cotisations.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.cotisations.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Cotisations::findOrFail($id);

        return view('legacy_migrated.cotisations.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'include_old' => 'nullable|string|max:255',
            'check_all_box' => 'nullable|string|max:255',
            'date_' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'type_paiement' => 'nullable|string|max:255',
            'periode' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
            'paid' => 'nullable|string|max:255',
        ]);

        $item = Cotisations::create([
            'sub' => $validated['sub'] ?? null,
            'include_old' => $validated['include_old'] ?? null,
            'check_all_box' => $validated['check_all_box'] ?? null,
            'date_' => $validated['date_'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'type_paiement' => $validated['type_paiement'] ?? null,
            'periode' => $validated['periode'] ?? null,
            'year' => $validated['year'] ?? null,
            'paid' => $validated['paid'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.cotisations.edit', $item->id)
            ->with('success', 'Cotisations created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Cotisations::findOrFail($id);

        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'include_old' => 'nullable|string|max:255',
            'check_all_box' => 'nullable|string|max:255',
            'date_' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'type_paiement' => 'nullable|string|max:255',
            'periode' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
            'paid' => 'nullable|string|max:255',
        ]);

        $item->update([
            'sub' => $validated['sub'] ?? null,
            'include_old' => $validated['include_old'] ?? null,
            'check_all_box' => $validated['check_all_box'] ?? null,
            'date_' => $validated['date_'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'type_paiement' => $validated['type_paiement'] ?? null,
            'periode' => $validated['periode'] ?? null,
            'year' => $validated['year'] ?? null,
            'paid' => $validated['paid'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.cotisations.edit', $item->id)
            ->with('success', 'Cotisations updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Cotisations::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.cotisations.index')
            ->with('success', 'Cotisations deleted successfully');
    }
                
}
