<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EvenementGarde;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_garde.php
 * Legacy pattern: generic
 * Legacy permission id: 6
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementGardeController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementGarde::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

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

        return view('legacy_migrated.evenement_garde.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.evenement_garde.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EvenementGarde::findOrFail($id);

        return view('legacy_migrated.evenement_garde.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'show_spp' => 'nullable|string|max:255',
            'show_indispos' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'total2' => 'nullable|string|max:255',
            'display_order' => 'nullable|string|max:255',
        ]);

        $item = EvenementGarde::create([
            'show_spp' => $validated['show_spp'] ?? null,
            'show_indispos' => $validated['show_indispos'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'total2' => $validated['total2'] ?? null,
            'display_order' => $validated['display_order'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_garde.edit', $item->id)
            ->with('success', 'EvenementGarde created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EvenementGarde::findOrFail($id);

        $validated = $request->validate([
            'show_spp' => 'nullable|string|max:255',
            'show_indispos' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'total2' => 'nullable|string|max:255',
            'display_order' => 'nullable|string|max:255',
        ]);

        $item->update([
            'show_spp' => $validated['show_spp'] ?? null,
            'show_indispos' => $validated['show_indispos'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'total2' => $validated['total2'] ?? null,
            'display_order' => $validated['display_order'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_garde.edit', $item->id)
            ->with('success', 'EvenementGarde updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EvenementGarde::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenement_garde.index')
            ->with('success', 'EvenementGarde deleted successfully');
    }
                
}
