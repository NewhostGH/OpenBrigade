<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\GmapsPersonnel;
use Illuminate\Http\Request;

/**
 * Legacy migration source: gmaps_personnel.php
 * Legacy pattern: list
 * Legacy permission id: 76
 * This file stems from a legacy migration and requires functional verification.
 */
class GmapsPersonnelController extends Controller
{
    public function index(Request $request)
    {
        $query = GmapsPersonnel::query();
        if (session()->has('SES_COMPANY')) {
            $query->where('company_id', session('SES_COMPANY'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_id', 'like', '%' . $term . '%');
                $query->orWhere('p_code', 'like', '%' . $term . '%');
                $query->orWhere('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.gmaps_personnel.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.gmaps_personnel.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = GmapsPersonnel::findOrFail($id);

        return view('legacy_migrated.gmaps_personnel.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'competence' => 'nullable|string|max:255',
        ]);

        $item = GmapsPersonnel::create([
            'sub' => $validated['sub'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'competence' => $validated['competence'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.gmaps_personnel.edit', $item->id)
            ->with('success', 'GmapsPersonnel created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = GmapsPersonnel::findOrFail($id);

        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'competence' => 'nullable|string|max:255',
        ]);

        $item->update([
            'sub' => $validated['sub'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'competence' => $validated['competence'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.gmaps_personnel.edit', $item->id)
            ->with('success', 'GmapsPersonnel updated successfully');
    }
                

    public function destroy($id)
    {
        $item = GmapsPersonnel::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.gmaps_personnel.index')
            ->with('success', 'GmapsPersonnel deleted successfully');
    }
                
}
