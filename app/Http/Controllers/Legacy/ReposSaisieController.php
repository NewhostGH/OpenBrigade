<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\ReposSaisie;
use Illuminate\Http\Request;

/**
 * Legacy migration source: repos_saisie.php
 * Legacy pattern: list
 * Legacy permission id: 10
 * This file stems from a legacy migration and requires functional verification.
 */
class ReposSaisieController extends Controller
{
    public function index(Request $request)
    {
        $query = ReposSaisie::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('valuedisplay_children21', 'like', '%' . $term . '%');
                $query->orWhere('0', 'like', '%' . $term . '%');
                $query->orWhere('section', 'like', '%' . $term . '%');
                $query->orWhere('5', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.repos_saisie.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.repos_saisie.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = ReposSaisie::findOrFail($id);

        return view('legacy_migrated.repos_saisie.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nbjours' => 'nullable|string|max:255',
            'person' => 'nullable|string|max:255',
            'month' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
            '2_' => 'nullable|string|max:255',
            '4_' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'filtre' => 'nullable|string|max:255',
            'menu1' => 'nullable|string|max:255',
            'menu2' => 'nullable|string|max:255',
        ]);

        $item = ReposSaisie::create([
            'nbjours' => $validated['nbjours'] ?? null,
            'person' => $validated['person'] ?? null,
            'month' => $validated['month'] ?? null,
            'year' => $validated['year'] ?? null,
            '2_' => $validated['2_'] ?? null,
            '4_' => $validated['4_'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'filtre' => $validated['filtre'] ?? null,
            'menu1' => $validated['menu1'] ?? null,
            'menu2' => $validated['menu2'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.repos_saisie.edit', $item->id)
            ->with('success', 'ReposSaisie created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = ReposSaisie::findOrFail($id);

        $validated = $request->validate([
            'nbjours' => 'nullable|string|max:255',
            'person' => 'nullable|string|max:255',
            'month' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
            '2_' => 'nullable|string|max:255',
            '4_' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'filtre' => 'nullable|string|max:255',
            'menu1' => 'nullable|string|max:255',
            'menu2' => 'nullable|string|max:255',
        ]);

        $item->update([
            'nbjours' => $validated['nbjours'] ?? null,
            'person' => $validated['person'] ?? null,
            'month' => $validated['month'] ?? null,
            'year' => $validated['year'] ?? null,
            '2_' => $validated['2_'] ?? null,
            '4_' => $validated['4_'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'filtre' => $validated['filtre'] ?? null,
            'menu1' => $validated['menu1'] ?? null,
            'menu2' => $validated['menu2'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.repos_saisie.edit', $item->id)
            ->with('success', 'ReposSaisie updated successfully');
    }
                

    public function destroy($id)
    {
        $item = ReposSaisie::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.repos_saisie.index')
            ->with('success', 'ReposSaisie deleted successfully');
    }
                
}
