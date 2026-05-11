<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Personnel;
use Illuminate\Http\Request;

/**
 * Legacy migration source: personnel.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class PersonnelController extends Controller
{
    public function index(Request $request)
    {
        $query = Personnel::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }
        if (session()->has('SES_COMPANY')) {
            $query->where('company_id', session('SES_COMPANY'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('photo', 'like', '%' . $term . '%');
                $query->orWhere('profession', 'like', '%' . $term . '%');
                $query->orWhere('grade', 'like', '%' . $term . '%');
                $query->orWhere('nom_prnom', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.personnel.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.personnel.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Personnel::findOrFail($id);

        return view('legacy_migrated.personnel.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'company2' => 'nullable|string|max:255',
            'category_filter' => 'nullable|string|max:255',
            'position_filter' => 'nullable|string|max:255',
        ]);

        $item = Personnel::create([
            'sub' => $validated['sub'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'company2' => $validated['company2'] ?? null,
            'category_filter' => $validated['category_filter'] ?? null,
            'position_filter' => $validated['position_filter'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.personnel.edit', $item->id)
            ->with('success', 'Personnel created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Personnel::findOrFail($id);

        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'company2' => 'nullable|string|max:255',
            'category_filter' => 'nullable|string|max:255',
            'position_filter' => 'nullable|string|max:255',
        ]);

        $item->update([
            'sub' => $validated['sub'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'company2' => $validated['company2'] ?? null,
            'category_filter' => $validated['category_filter'] ?? null,
            'position_filter' => $validated['position_filter'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.personnel.edit', $item->id)
            ->with('success', 'Personnel updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Personnel::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.personnel.index')
            ->with('success', 'Personnel deleted successfully');
    }
                
}
