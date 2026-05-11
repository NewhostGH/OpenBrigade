<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\DemandeRenfort;
use Illuminate\Http\Request;

/**
 * Legacy migration source: demande_renfort.php
 * Legacy pattern: list
 * Legacy permission id: 41
 * This file stems from a legacy migration and requires functional verification.
 */
class DemandeRenfortController extends Controller
{
    public function index(Request $request)
    {
        $query = DemandeRenfort::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('sumnb_vehicules', 'like', '%' . $term . '%');
                $query->orWhere('te_code', 'like', '%' . $term . '%');
                $query->orWhere('e_libelle', 'like', '%' . $term . '%');
                $query->orWhere('e_closed', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.demande_renfort.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.demande_renfort.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = DemandeRenfort::findOrFail($id);

        return view('legacy_migrated.demande_renfort.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'evenement' => 'nullable|string|max:255',
            'vehicule' => 'nullable|string|max:255',
            'type_' => 'nullable|string|max:255',
            '$TM_USAGE' => 'nullable|string|max:255',
            'point' => 'nullable|string|max:255',
            'specifique' => 'nullable|string|max:255',
            'new_type_vehicule' => 'nullable|string|max:255',
            'new_type_materiel' => 'nullable|string|max:255',
        ]);

        $item = DemandeRenfort::create([
            'evenement' => $validated['evenement'] ?? null,
            'vehicule' => $validated['vehicule'] ?? null,
            'type_' => $validated['type_'] ?? null,
            '$TM_USAGE' => $validated['$TM_USAGE'] ?? null,
            'point' => $validated['point'] ?? null,
            'specifique' => $validated['specifique'] ?? null,
            'new_type_vehicule' => $validated['new_type_vehicule'] ?? null,
            'new_type_materiel' => $validated['new_type_materiel'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.demande_renfort.edit', $item->id)
            ->with('success', 'DemandeRenfort created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = DemandeRenfort::findOrFail($id);

        $validated = $request->validate([
            'evenement' => 'nullable|string|max:255',
            'vehicule' => 'nullable|string|max:255',
            'type_' => 'nullable|string|max:255',
            '$TM_USAGE' => 'nullable|string|max:255',
            'point' => 'nullable|string|max:255',
            'specifique' => 'nullable|string|max:255',
            'new_type_vehicule' => 'nullable|string|max:255',
            'new_type_materiel' => 'nullable|string|max:255',
        ]);

        $item->update([
            'evenement' => $validated['evenement'] ?? null,
            'vehicule' => $validated['vehicule'] ?? null,
            'type_' => $validated['type_'] ?? null,
            '$TM_USAGE' => $validated['$TM_USAGE'] ?? null,
            'point' => $validated['point'] ?? null,
            'specifique' => $validated['specifique'] ?? null,
            'new_type_vehicule' => $validated['new_type_vehicule'] ?? null,
            'new_type_materiel' => $validated['new_type_materiel'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.demande_renfort.edit', $item->id)
            ->with('success', 'DemandeRenfort updated successfully');
    }
                

    public function destroy($id)
    {
        $item = DemandeRenfort::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.demande_renfort.index')
            ->with('success', 'DemandeRenfort deleted successfully');
    }
                
}
