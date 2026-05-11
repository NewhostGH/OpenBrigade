<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\SearchPersonnel;
use Illuminate\Http\Request;

/**
 * Legacy migration source: search_personnel.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class SearchPersonnelController extends Controller
{
    public function index(Request $request)
    {
        $query = SearchPersonnel::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('datastylebtndefaultdatacontainerbodyifcheck_rightsid', 'like', '%' . $term . '%');
                $query->orWhere('40local_onlyfalseelselocal_onlytrueiflocal_onlyhighestsectionget_highest_section_where_grantedid', 'like', '%' . $term . '%');
                $query->orWhere('optiondisplay_children2highestsection', 'like', '%' . $term . '%');
                $query->orWhere('level1', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.search_personnel.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.search_personnel.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = SearchPersonnel::findOrFail($id);

        return view('legacy_migrated.search_personnel.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'choixSection' => 'nullable|string|max:255',
            'trouve' => 'nullable|string|max:255',
            'typetri' => 'nullable|string|max:255',
            'selectComp' => 'nullable|string|max:255',
            'choixStatut' => 'nullable|string|max:255',
            'typeTri' => 'nullable|string|max:255',
        ]);

        $item = SearchPersonnel::create([
            'choixSection' => $validated['choixSection'] ?? null,
            'trouve' => $validated['trouve'] ?? null,
            'typetri' => $validated['typetri'] ?? null,
            'selectComp' => $validated['selectComp'] ?? null,
            'choixStatut' => $validated['choixStatut'] ?? null,
            'typeTri' => $validated['typeTri'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.search_personnel.edit', $item->id)
            ->with('success', 'SearchPersonnel created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = SearchPersonnel::findOrFail($id);

        $validated = $request->validate([
            'choixSection' => 'nullable|string|max:255',
            'trouve' => 'nullable|string|max:255',
            'typetri' => 'nullable|string|max:255',
            'selectComp' => 'nullable|string|max:255',
            'choixStatut' => 'nullable|string|max:255',
            'typeTri' => 'nullable|string|max:255',
        ]);

        $item->update([
            'choixSection' => $validated['choixSection'] ?? null,
            'trouve' => $validated['trouve'] ?? null,
            'typetri' => $validated['typetri'] ?? null,
            'selectComp' => $validated['selectComp'] ?? null,
            'choixStatut' => $validated['choixStatut'] ?? null,
            'typeTri' => $validated['typeTri'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.search_personnel.edit', $item->id)
            ->with('success', 'SearchPersonnel updated successfully');
    }
                

    public function destroy($id)
    {
        $item = SearchPersonnel::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.search_personnel.index')
            ->with('success', 'SearchPersonnel deleted successfully');
    }
                
}
