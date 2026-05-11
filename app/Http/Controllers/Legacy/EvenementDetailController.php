<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EvenementDetail;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_detail.php
 * Legacy pattern: list
 * Legacy permission id: 41
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementDetailController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementDetail::query();
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

        return view('legacy_migrated.evenement_detail.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.evenement_detail.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EvenementDetail::findOrFail($id);

        return view('legacy_migrated.evenement_detail.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'autorefresh' => 'nullable|string|max:255',
            'sectioninscription' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'add' => 'nullable|string|max:255',
            'newchef' => 'nullable|string|max:255',
            'addvehicule' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'addmateriel' => 'nullable|string|max:255',
            'addconso' => 'nullable|string|max:255',
        ]);

        $item = EvenementDetail::create([
            'sub' => $validated['sub'] ?? null,
            'autorefresh' => $validated['autorefresh'] ?? null,
            'sectioninscription' => $validated['sectioninscription'] ?? null,
            'company' => $validated['company'] ?? null,
            'add' => $validated['add'] ?? null,
            'newchef' => $validated['newchef'] ?? null,
            'addvehicule' => $validated['addvehicule'] ?? null,
            'type' => $validated['type'] ?? null,
            'addmateriel' => $validated['addmateriel'] ?? null,
            'addconso' => $validated['addconso'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_detail.edit', $item->id)
            ->with('success', 'EvenementDetail created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EvenementDetail::findOrFail($id);

        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'autorefresh' => 'nullable|string|max:255',
            'sectioninscription' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'add' => 'nullable|string|max:255',
            'newchef' => 'nullable|string|max:255',
            'addvehicule' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'addmateriel' => 'nullable|string|max:255',
            'addconso' => 'nullable|string|max:255',
        ]);

        $item->update([
            'sub' => $validated['sub'] ?? null,
            'autorefresh' => $validated['autorefresh'] ?? null,
            'sectioninscription' => $validated['sectioninscription'] ?? null,
            'company' => $validated['company'] ?? null,
            'add' => $validated['add'] ?? null,
            'newchef' => $validated['newchef'] ?? null,
            'addvehicule' => $validated['addvehicule'] ?? null,
            'type' => $validated['type'] ?? null,
            'addmateriel' => $validated['addmateriel'] ?? null,
            'addconso' => $validated['addconso'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_detail.edit', $item->id)
            ->with('success', 'EvenementDetail updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EvenementDetail::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenement_detail.index')
            ->with('success', 'EvenementDetail deleted successfully');
    }
                
}
