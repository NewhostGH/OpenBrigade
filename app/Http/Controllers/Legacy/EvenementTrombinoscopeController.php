<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EvenementTrombinoscope;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_trombinoscope.php
 * Legacy pattern: list
 * Legacy permission id: 44
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementTrombinoscopeController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementTrombinoscope::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('e_code', 'like', '%' . $term . '%');
                $query->orWhere('s_id', 'like', '%' . $term . '%');
                $query->orWhere('te_code', 'like', '%' . $term . '%');
                $query->orWhere('te_libelle', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.evenement_trombinoscope.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.evenement_trombinoscope.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EvenementTrombinoscope::findOrFail($id);

        return view('legacy_migrated.evenement_trombinoscope.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = EvenementTrombinoscope::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.evenement_trombinoscope.edit', $item->id)
            ->with('success', 'EvenementTrombinoscope created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EvenementTrombinoscope::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.evenement_trombinoscope.edit', $item->id)
            ->with('success', 'EvenementTrombinoscope updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EvenementTrombinoscope::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenement_trombinoscope.index')
            ->with('success', 'EvenementTrombinoscope deleted successfully');
    }
                
}
