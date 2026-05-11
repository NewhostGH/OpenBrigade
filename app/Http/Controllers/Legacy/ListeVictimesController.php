<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\ListeVictimes;
use Illuminate\Http\Request;

/**
 * Legacy migration source: liste_victimes.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class ListeVictimesController extends Controller
{
    public function index(Request $request)
    {
        $query = ListeVictimes::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('cav_ouvert', 'like', '%' . $term . '%');
                $query->orWhere('cav_responsable', 'like', '%' . $term . '%');
                $query->orWhere('e_code', 'like', '%' . $term . '%');
                $query->orWhere('count', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.liste_victimes.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.liste_victimes.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = ListeVictimes::findOrFail($id);

        return view('legacy_migrated.liste_victimes.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ajouter' => 'nullable|string|max:255',
            'in_cav' => 'nullable|string|max:255',
            'a_reguler' => 'nullable|string|max:255',
            'autorefresh' => 'nullable|string|max:255',
            'type_victime' => 'nullable|string|max:255',
        ]);

        $item = ListeVictimes::create([
            'ajouter' => $validated['ajouter'] ?? null,
            'in_cav' => $validated['in_cav'] ?? null,
            'a_reguler' => $validated['a_reguler'] ?? null,
            'autorefresh' => $validated['autorefresh'] ?? null,
            'type_victime' => $validated['type_victime'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.liste_victimes.edit', $item->id)
            ->with('success', 'ListeVictimes created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = ListeVictimes::findOrFail($id);

        $validated = $request->validate([
            'ajouter' => 'nullable|string|max:255',
            'in_cav' => 'nullable|string|max:255',
            'a_reguler' => 'nullable|string|max:255',
            'autorefresh' => 'nullable|string|max:255',
            'type_victime' => 'nullable|string|max:255',
        ]);

        $item->update([
            'ajouter' => $validated['ajouter'] ?? null,
            'in_cav' => $validated['in_cav'] ?? null,
            'a_reguler' => $validated['a_reguler'] ?? null,
            'autorefresh' => $validated['autorefresh'] ?? null,
            'type_victime' => $validated['type_victime'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.liste_victimes.edit', $item->id)
            ->with('success', 'ListeVictimes updated successfully');
    }
                

    public function destroy($id)
    {
        $item = ListeVictimes::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.liste_victimes.index')
            ->with('success', 'ListeVictimes deleted successfully');
    }
                
}
