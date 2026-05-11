<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\FonctionsParameters;
use Illuminate\Http\Request;

/**
 * Legacy migration source: fonctions_parameters.php
 * Legacy pattern: generic
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class FonctionsParametersController extends Controller
{
    public function index(Request $request)
    {
        $query = FonctionsParameters::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('counteq_id', 'like', '%' . $term . '%');
                $query->orWhere('mineq_id', 'like', '%' . $term . '%');
                $query->orWhere('eq_id', 'like', '%' . $term . '%');
                $query->orWhere('maxeq_id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.fonctions_parameters.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.fonctions_parameters.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = FonctionsParameters::findOrFail($id);

        return view('legacy_migrated.fonctions_parameters.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'modevictime' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
        ]);

        $item = FonctionsParameters::create([
            'modevictime' => $validated['modevictime'] ?? null,
            'filter' => $validated['filter'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.fonctions_parameters.edit', $item->id)
            ->with('success', 'FonctionsParameters created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = FonctionsParameters::findOrFail($id);

        $validated = $request->validate([
            'modevictime' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
        ]);

        $item->update([
            'modevictime' => $validated['modevictime'] ?? null,
            'filter' => $validated['filter'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.fonctions_parameters.edit', $item->id)
            ->with('success', 'FonctionsParameters updated successfully');
    }
                

    public function destroy($id)
    {
        $item = FonctionsParameters::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.fonctions_parameters.index')
            ->with('success', 'FonctionsParameters deleted successfully');
    }
                
}
