<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EvenementMultiRenforts;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_multi_renforts.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementMultiRenfortsController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementMultiRenforts::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('s_id', 'like', '%' . $term . '%');
                $query->orWhere('e_parent', 'like', '%' . $term . '%');
                $query->orWhere('e_allow_reinforcement', 'like', '%' . $term . '%');
                $query->orWhere('e_canceled', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.evenement_multi_renforts.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.evenement_multi_renforts.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EvenementMultiRenforts::findOrFail($id);

        return view('legacy_migrated.evenement_multi_renforts.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'yesall' => 'nullable|string|max:255',
            'noall' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'confirmed' => 'nullable|string|max:255',
            'check_' => 'nullable|string|max:255',
        ]);

        $item = EvenementMultiRenforts::create([
            'yesall' => $validated['yesall'] ?? null,
            'noall' => $validated['noall'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'confirmed' => $validated['confirmed'] ?? null,
            'check_' => $validated['check_'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_multi_renforts.edit', $item->id)
            ->with('success', 'EvenementMultiRenforts created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EvenementMultiRenforts::findOrFail($id);

        $validated = $request->validate([
            'yesall' => 'nullable|string|max:255',
            'noall' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'confirmed' => 'nullable|string|max:255',
            'check_' => 'nullable|string|max:255',
        ]);

        $item->update([
            'yesall' => $validated['yesall'] ?? null,
            'noall' => $validated['noall'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'confirmed' => $validated['confirmed'] ?? null,
            'check_' => $validated['check_'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_multi_renforts.edit', $item->id)
            ->with('success', 'EvenementMultiRenforts updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EvenementMultiRenforts::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenement_multi_renforts.index')
            ->with('success', 'EvenementMultiRenforts deleted successfully');
    }
                
}
