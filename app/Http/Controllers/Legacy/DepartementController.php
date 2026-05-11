<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Departement;
use Illuminate\Http\Request;

/**
 * Legacy migration source: departement.php
 * Legacy pattern: list
 * Legacy permission id: 52
 * This file stems from a legacy migration and requires functional verification.
 */
class DepartementController extends Controller
{
    public function index(Request $request)
    {
        $query = Departement::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('count1', 'like', '%' . $term . '%');
                $query->orWhere('count', 'like', '%' . $term . '%');
                $query->orWhere('s_code', 'like', '%' . $term . '%');
                $query->orWhere('s_description', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.departement.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.departement.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Departement::findOrFail($id);

        return view('legacy_migrated.departement.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'filter' => 'nullable|string|max:255',
            'niv' => 'nullable|string|max:255',
        ]);

        $item = Departement::create([
            'filter' => $validated['filter'] ?? null,
            'niv' => $validated['niv'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.departement.edit', $item->id)
            ->with('success', 'Departement created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Departement::findOrFail($id);

        $validated = $request->validate([
            'filter' => 'nullable|string|max:255',
            'niv' => 'nullable|string|max:255',
        ]);

        $item->update([
            'filter' => $validated['filter'] ?? null,
            'niv' => $validated['niv'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.departement.edit', $item->id)
            ->with('success', 'Departement updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Departement::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.departement.index')
            ->with('success', 'Departement deleted successfully');
    }
                
}
