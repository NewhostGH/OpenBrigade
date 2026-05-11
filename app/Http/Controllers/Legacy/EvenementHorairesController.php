<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EvenementHoraires;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_horaires.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementHorairesController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementHoraires::query();
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

        return view('legacy_migrated.evenement_horaires.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.evenement_horaires.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EvenementHoraires::findOrFail($id);

        return view('legacy_migrated.evenement_horaires.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'evenement' => 'nullable|string|max:255',
            'pid' => 'nullable|string|max:255',
            'vid' => 'nullable|string|max:255',
            'debut_$k' => 'nullable|string|max:255',
            'fin_$k' => 'nullable|string|max:255',
        ]);

        $item = EvenementHoraires::create([
            'evenement' => $validated['evenement'] ?? null,
            'pid' => $validated['pid'] ?? null,
            'vid' => $validated['vid'] ?? null,
            'debut_$k' => $validated['debut_$k'] ?? null,
            'fin_$k' => $validated['fin_$k'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_horaires.edit', $item->id)
            ->with('success', 'EvenementHoraires created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EvenementHoraires::findOrFail($id);

        $validated = $request->validate([
            'evenement' => 'nullable|string|max:255',
            'pid' => 'nullable|string|max:255',
            'vid' => 'nullable|string|max:255',
            'debut_$k' => 'nullable|string|max:255',
            'fin_$k' => 'nullable|string|max:255',
        ]);

        $item->update([
            'evenement' => $validated['evenement'] ?? null,
            'pid' => $validated['pid'] ?? null,
            'vid' => $validated['vid'] ?? null,
            'debut_$k' => $validated['debut_$k'] ?? null,
            'fin_$k' => $validated['fin_$k'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_horaires.edit', $item->id)
            ->with('success', 'EvenementHoraires updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EvenementHoraires::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenement_horaires.index')
            ->with('success', 'EvenementHoraires deleted successfully');
    }
                
}
