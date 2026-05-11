<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\PersonnelMaitre;
use Illuminate\Http\Request;

/**
 * Legacy migration source: personnel_maitre.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class PersonnelMaitreController extends Controller
{
    public function index(Request $request)
    {
        $query = PersonnelMaitre::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
                $query->orWhere('p_maitre', 'like', '%' . $term . '%');
                $query->orWhere('p_id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.personnel_maitre.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.personnel_maitre.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = PersonnelMaitre::findOrFail($id);

        return view('legacy_migrated.personnel_maitre.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'newmaitre' => 'nullable|string|max:255',
        ]);

        $item = PersonnelMaitre::create([
            'newmaitre' => $validated['newmaitre'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.personnel_maitre.edit', $item->id)
            ->with('success', 'PersonnelMaitre created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = PersonnelMaitre::findOrFail($id);

        $validated = $request->validate([
            'newmaitre' => 'nullable|string|max:255',
        ]);

        $item->update([
            'newmaitre' => $validated['newmaitre'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.personnel_maitre.edit', $item->id)
            ->with('success', 'PersonnelMaitre updated successfully');
    }
                

    public function destroy($id)
    {
        $item = PersonnelMaitre::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.personnel_maitre.index')
            ->with('success', 'PersonnelMaitre deleted successfully');
    }
                
}
