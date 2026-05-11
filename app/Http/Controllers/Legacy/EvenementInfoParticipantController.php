<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EvenementInfoParticipant;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_info_participant.php
 * Legacy pattern: generic
 * Legacy permission id: 41
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementInfoParticipantController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementInfoParticipant::query();

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

        return view('legacy_migrated.evenement_info_participant.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.evenement_info_participant.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EvenementInfoParticipant::findOrFail($id);

        return view('legacy_migrated.evenement_info_participant.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'EP_FLAG1' => 'nullable|string|max:255',
            'p' => 'nullable|string|max:255',
            'detail' => 'nullable|string|max:255',
        ]);

        $item = EvenementInfoParticipant::create([
            'EP_FLAG1' => $validated['EP_FLAG1'] ?? null,
            'p' => $validated['p'] ?? null,
            'detail' => $validated['detail'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_info_participant.edit', $item->id)
            ->with('success', 'EvenementInfoParticipant created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EvenementInfoParticipant::findOrFail($id);

        $validated = $request->validate([
            'EP_FLAG1' => 'nullable|string|max:255',
            'p' => 'nullable|string|max:255',
            'detail' => 'nullable|string|max:255',
        ]);

        $item->update([
            'EP_FLAG1' => $validated['EP_FLAG1'] ?? null,
            'p' => $validated['p'] ?? null,
            'detail' => $validated['detail'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_info_participant.edit', $item->id)
            ->with('success', 'EvenementInfoParticipant updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EvenementInfoParticipant::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenement_info_participant.index')
            ->with('success', 'EvenementInfoParticipant deleted successfully');
    }
                
}
