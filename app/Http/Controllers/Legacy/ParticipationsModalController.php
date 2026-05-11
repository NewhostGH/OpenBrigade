<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Participations;
use Illuminate\Http\Request;

/**
 * Legacy migration source: participations_modal.php
 * Legacy pattern: generic
 * Legacy permission id: 56
 * This file stems from a legacy migration and requires functional verification.
 */
class ParticipationsModalController extends Controller
{
    public function index(Request $request)
    {
        $query = Participations::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
                $query->orWhere('p_statut', 'like', '%' . $term . '%');
                $query->orWhere('e_code', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.participations_modal.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.participations_modal.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Participations::findOrFail($id);

        return view('legacy_migrated.participations_modal.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Participations::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.participations_modal.edit', $item->id)
            ->with('success', 'Participations created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Participations::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.participations_modal.edit', $item->id)
            ->with('success', 'Participations updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Participations::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.participations_modal.index')
            ->with('success', 'Participations deleted successfully');
    }
                
}
