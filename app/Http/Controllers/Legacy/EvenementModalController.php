<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Evenement;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_modal.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementModalController extends Controller
{
    public function index(Request $request)
    {
        $query = Evenement::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
                $query->orWhere('p_sexe', 'like', '%' . $term . '%');
                $query->orWhere('p_email', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.evenement_modal.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.evenement_modal.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Evenement::findOrFail($id);

        return view('legacy_migrated.evenement_modal.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            's' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'fn' => 'nullable|string|max:255',
            'vfn' => 'nullable|string|max:255',
            'pe' => 'nullable|string|max:255',
        ]);

        $item = Evenement::create([
            's' => $validated['s'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'fn' => $validated['fn'] ?? null,
            'vfn' => $validated['vfn'] ?? null,
            'pe' => $validated['pe'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_modal.edit', $item->id)
            ->with('success', 'Evenement created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Evenement::findOrFail($id);

        $validated = $request->validate([
            's' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'fn' => 'nullable|string|max:255',
            'vfn' => 'nullable|string|max:255',
            'pe' => 'nullable|string|max:255',
        ]);

        $item->update([
            's' => $validated['s'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'fn' => $validated['fn'] ?? null,
            'vfn' => $validated['vfn'] ?? null,
            'pe' => $validated['pe'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_modal.edit', $item->id)
            ->with('success', 'Evenement updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Evenement::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenement_modal.index')
            ->with('success', 'Evenement deleted successfully');
    }
                
}
