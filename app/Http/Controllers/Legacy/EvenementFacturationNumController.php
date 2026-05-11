<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EvenementFacturationNum;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_facturation_num.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementFacturationNumController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementFacturationNum::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('e_id', 'like', '%' . $term . '%');
                $query->orWhere('facture_numero', 'like', '%' . $term . '%');
                $query->orWhere('e_libelle', 'like', '%' . $term . '%');
                $query->orWhere('s_id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.evenement_facturation_num.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.evenement_facturation_num.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EvenementFacturationNum::findOrFail($id);

        return view('legacy_migrated.evenement_facturation_num.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'trouve' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
        ]);

        $item = EvenementFacturationNum::create([
            'trouve' => $validated['trouve'] ?? null,
            'section' => $validated['section'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_facturation_num.edit', $item->id)
            ->with('success', 'EvenementFacturationNum created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EvenementFacturationNum::findOrFail($id);

        $validated = $request->validate([
            'trouve' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
        ]);

        $item->update([
            'trouve' => $validated['trouve'] ?? null,
            'section' => $validated['section'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_facturation_num.edit', $item->id)
            ->with('success', 'EvenementFacturationNum updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EvenementFacturationNum::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenement_facturation_num.index')
            ->with('success', 'EvenementFacturationNum deleted successfully');
    }
                
}
