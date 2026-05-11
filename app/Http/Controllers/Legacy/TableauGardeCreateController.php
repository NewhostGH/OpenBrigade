<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\TableauGardeCreate;
use Illuminate\Http\Request;

/**
 * Legacy migration source: tableau_garde_create.php
 * Legacy pattern: list
 * Legacy permission id: 5
 * This file stems from a legacy migration and requires functional verification.
 */
class TableauGardeCreateController extends Controller
{
    public function index(Request $request)
    {
        $query = TableauGardeCreate::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('eq_nom', 'like', '%' . $term . '%');
                $query->orWhere('eq_jour', 'like', '%' . $term . '%');
                $query->orWhere('eq_nuit', 'like', '%' . $term . '%');
                $query->orWhere('s_id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.tableau_garde_create.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.tableau_garde_create.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = TableauGardeCreate::findOrFail($id);

        return view('legacy_migrated.tableau_garde_create.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'g2p' => 'nullable|string|max:255',
            'defaultpart' => 'nullable|string|max:255',
            'month' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
            'equipe' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'date1' => 'nullable|string|max:255',
            'date2' => 'nullable|string|max:255',
            'alldays' => 'nullable|string|max:255',
            'V' => 'nullable|string|max:255',
            'SPP' => 'nullable|string|max:255',
            'SPV' => 'nullable|string|max:255',
            'day_choice' => 'nullable|string|max:255',
            'lieu' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'debut1' => 'nullable|string|max:255',
            'fin1' => 'nullable|string|max:255',
            'duree1' => 'nullable|string|max:255',
            'nb1' => 'nullable|string|max:255',
            'debut2' => 'nullable|string|max:255',
        ]);

        $item = TableauGardeCreate::create([
            'g2p' => $validated['g2p'] ?? null,
            'defaultpart' => $validated['defaultpart'] ?? null,
            'month' => $validated['month'] ?? null,
            'year' => $validated['year'] ?? null,
            'equipe' => $validated['equipe'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'date1' => $validated['date1'] ?? null,
            'date2' => $validated['date2'] ?? null,
            'alldays' => $validated['alldays'] ?? null,
            'V' => $validated['V'] ?? null,
            'SPP' => $validated['SPP'] ?? null,
            'SPV' => $validated['SPV'] ?? null,
            'day_choice' => $validated['day_choice'] ?? null,
            'lieu' => $validated['lieu'] ?? null,
            'address' => $validated['address'] ?? null,
            'debut1' => $validated['debut1'] ?? null,
            'fin1' => $validated['fin1'] ?? null,
            'duree1' => $validated['duree1'] ?? null,
            'nb1' => $validated['nb1'] ?? null,
            'debut2' => $validated['debut2'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.tableau_garde_create.edit', $item->id)
            ->with('success', 'TableauGardeCreate created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = TableauGardeCreate::findOrFail($id);

        $validated = $request->validate([
            'g2p' => 'nullable|string|max:255',
            'defaultpart' => 'nullable|string|max:255',
            'month' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
            'equipe' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'date1' => 'nullable|string|max:255',
            'date2' => 'nullable|string|max:255',
            'alldays' => 'nullable|string|max:255',
            'V' => 'nullable|string|max:255',
            'SPP' => 'nullable|string|max:255',
            'SPV' => 'nullable|string|max:255',
            'day_choice' => 'nullable|string|max:255',
            'lieu' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'debut1' => 'nullable|string|max:255',
            'fin1' => 'nullable|string|max:255',
            'duree1' => 'nullable|string|max:255',
            'nb1' => 'nullable|string|max:255',
            'debut2' => 'nullable|string|max:255',
        ]);

        $item->update([
            'g2p' => $validated['g2p'] ?? null,
            'defaultpart' => $validated['defaultpart'] ?? null,
            'month' => $validated['month'] ?? null,
            'year' => $validated['year'] ?? null,
            'equipe' => $validated['equipe'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'date1' => $validated['date1'] ?? null,
            'date2' => $validated['date2'] ?? null,
            'alldays' => $validated['alldays'] ?? null,
            'V' => $validated['V'] ?? null,
            'SPP' => $validated['SPP'] ?? null,
            'SPV' => $validated['SPV'] ?? null,
            'day_choice' => $validated['day_choice'] ?? null,
            'lieu' => $validated['lieu'] ?? null,
            'address' => $validated['address'] ?? null,
            'debut1' => $validated['debut1'] ?? null,
            'fin1' => $validated['fin1'] ?? null,
            'duree1' => $validated['duree1'] ?? null,
            'nb1' => $validated['nb1'] ?? null,
            'debut2' => $validated['debut2'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.tableau_garde_create.edit', $item->id)
            ->with('success', 'TableauGardeCreate updated successfully');
    }
                

    public function destroy($id)
    {
        $item = TableauGardeCreate::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.tableau_garde_create.index')
            ->with('success', 'TableauGardeCreate deleted successfully');
    }
                
}
