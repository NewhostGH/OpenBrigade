<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EvenementConsommable;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_consommable.php
 * Legacy pattern: generic
 * Legacy permission id: 42
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementConsommableController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementConsommable::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('c_id', 'like', '%' . $term . '%');
                $query->orWhere('s_id', 'like', '%' . $term . '%');
                $query->orWhere('tc_id', 'like', '%' . $term . '%');
                $query->orWhere('c_description', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.evenement_consommable.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.evenement_consommable.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EvenementConsommable::findOrFail($id);

        return view('legacy_migrated.evenement_consommable.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'annuler' => 'nullable|string|max:255',
        ]);

        $item = EvenementConsommable::create([
            'annuler' => $validated['annuler'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_consommable.edit', $item->id)
            ->with('success', 'EvenementConsommable created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EvenementConsommable::findOrFail($id);

        $validated = $request->validate([
            'annuler' => 'nullable|string|max:255',
        ]);

        $item->update([
            'annuler' => $validated['annuler'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_consommable.edit', $item->id)
            ->with('success', 'EvenementConsommable updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EvenementConsommable::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenement_consommable.index')
            ->with('success', 'EvenementConsommable deleted successfully');
    }
                
}
