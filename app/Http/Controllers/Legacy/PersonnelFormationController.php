<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\PersonnelFormation;
use Illuminate\Http\Request;

/**
 * Legacy migration source: personnel_formation.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class PersonnelFormationController extends Controller
{
    public function index(Request $request)
    {
        $query = PersonnelFormation::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('type', 'like', '%' . $term . '%');
                $query->orWhere('ps_diploma', 'like', '%' . $term . '%');
                $query->orWhere('ps_recycle', 'like', '%' . $term . '%');
                $query->orWhere('description', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.personnel_formation.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.personnel_formation.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = PersonnelFormation::findOrFail($id);

        return view('legacy_migrated.personnel_formation.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dc' => 'nullable|string|max:255',
            'lieu' => 'nullable|string|max:255',
            'resp' => 'nullable|string|max:255',
            'numdiplome' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:255',
            'tf' => 'nullable|string|max:255',
        ]);

        $item = PersonnelFormation::create([
            'dc' => $validated['dc'] ?? null,
            'lieu' => $validated['lieu'] ?? null,
            'resp' => $validated['resp'] ?? null,
            'numdiplome' => $validated['numdiplome'] ?? null,
            'comment' => $validated['comment'] ?? null,
            'tf' => $validated['tf'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.personnel_formation.edit', $item->id)
            ->with('success', 'PersonnelFormation created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = PersonnelFormation::findOrFail($id);

        $validated = $request->validate([
            'dc' => 'nullable|string|max:255',
            'lieu' => 'nullable|string|max:255',
            'resp' => 'nullable|string|max:255',
            'numdiplome' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:255',
            'tf' => 'nullable|string|max:255',
        ]);

        $item->update([
            'dc' => $validated['dc'] ?? null,
            'lieu' => $validated['lieu'] ?? null,
            'resp' => $validated['resp'] ?? null,
            'numdiplome' => $validated['numdiplome'] ?? null,
            'comment' => $validated['comment'] ?? null,
            'tf' => $validated['tf'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.personnel_formation.edit', $item->id)
            ->with('success', 'PersonnelFormation updated successfully');
    }
                

    public function destroy($id)
    {
        $item = PersonnelFormation::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.personnel_formation.index')
            ->with('success', 'PersonnelFormation deleted successfully');
    }
                
}
