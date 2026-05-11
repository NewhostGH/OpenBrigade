<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EvenementAddRenfort;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_add_renfort.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementAddRenfortController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementAddRenfort::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('e_code', 'like', '%' . $term . '%');
                $query->orWhere('e_parent', 'like', '%' . $term . '%');
                $query->orWhere('e_canceled', 'like', '%' . $term . '%');
                $query->orWhere('e_colonne_renfort', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.evenement_add_renfort.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.evenement_add_renfort.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EvenementAddRenfort::findOrFail($id);

        return view('legacy_migrated.evenement_add_renfort.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'evenement' => 'nullable|string|max:255',
            'renfort' => 'nullable|string|max:255',
            'confirmed' => 'nullable|string|max:255',
        ]);

        $item = EvenementAddRenfort::create([
            'evenement' => $validated['evenement'] ?? null,
            'renfort' => $validated['renfort'] ?? null,
            'confirmed' => $validated['confirmed'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_add_renfort.edit', $item->id)
            ->with('success', 'EvenementAddRenfort created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EvenementAddRenfort::findOrFail($id);

        $validated = $request->validate([
            'evenement' => 'nullable|string|max:255',
            'renfort' => 'nullable|string|max:255',
            'confirmed' => 'nullable|string|max:255',
        ]);

        $item->update([
            'evenement' => $validated['evenement'] ?? null,
            'renfort' => $validated['renfort'] ?? null,
            'confirmed' => $validated['confirmed'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_add_renfort.edit', $item->id)
            ->with('success', 'EvenementAddRenfort updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EvenementAddRenfort::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenement_add_renfort.index')
            ->with('success', 'EvenementAddRenfort deleted successfully');
    }
                
}
