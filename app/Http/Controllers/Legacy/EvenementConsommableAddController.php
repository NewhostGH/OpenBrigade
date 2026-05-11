<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EvenementConsommableAdd;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_consommable_add.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementConsommableAddController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementConsommableAdd::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('e_open_to_ext', 'like', '%' . $term . '%');
                $query->orWhere('s_id', 'like', '%' . $term . '%');
                $query->orWhere('c_nombre', 'like', '%' . $term . '%');
                $query->orWhere('ec_nombre', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.evenement_consommable_add.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.evenement_consommable_add.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EvenementConsommableAdd::findOrFail($id);

        return view('legacy_migrated.evenement_consommable_add.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'C_ID' => 'nullable|string|max:255',
            'EC_ID' => 'nullable|string|max:255',
            'nb' => 'nullable|string|max:255',
            'EC' => 'nullable|string|max:255',
        ]);

        $item = EvenementConsommableAdd::create([
            'from' => $validated['from'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'action' => $validated['action'] ?? null,
            'C_ID' => $validated['C_ID'] ?? null,
            'EC_ID' => $validated['EC_ID'] ?? null,
            'nb' => $validated['nb'] ?? null,
            'EC' => $validated['EC'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_consommable_add.edit', $item->id)
            ->with('success', 'EvenementConsommableAdd created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EvenementConsommableAdd::findOrFail($id);

        $validated = $request->validate([
            'from' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'C_ID' => 'nullable|string|max:255',
            'EC_ID' => 'nullable|string|max:255',
            'nb' => 'nullable|string|max:255',
            'EC' => 'nullable|string|max:255',
        ]);

        $item->update([
            'from' => $validated['from'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'action' => $validated['action'] ?? null,
            'C_ID' => $validated['C_ID'] ?? null,
            'EC_ID' => $validated['EC_ID'] ?? null,
            'nb' => $validated['nb'] ?? null,
            'EC' => $validated['EC'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_consommable_add.edit', $item->id)
            ->with('success', 'EvenementConsommableAdd updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EvenementConsommableAdd::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenement_consommable_add.index')
            ->with('success', 'EvenementConsommableAdd deleted successfully');
    }
                
}
