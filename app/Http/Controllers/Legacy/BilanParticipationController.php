<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\BilanParticipation;
use Illuminate\Http\Request;

/**
 * Legacy migration source: bilan_participation.php
 * Legacy pattern: list
 * Legacy permission id: 27
 * This file stems from a legacy migration and requires functional verification.
 */
class BilanParticipationController extends Controller
{
    public function index(Request $request)
    {
        $query = BilanParticipation::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('count1', 'like', '%' . $term . '%');
                $query->orWhere('e_code', 'like', '%' . $term . '%');
                $query->orWhere('value', 'like', '%' . $term . '%');
                $query->orWhere('c1', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.bilan_participation.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.bilan_participation.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = BilanParticipation::findOrFail($id);

        return view('legacy_migrated.bilan_participation.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'groupJN' => 'nullable|string|max:255',
            'c$i' => 'nullable|string|max:255',
            'c' => 'nullable|string|max:255',
            'c1' => 'nullable|string|max:255',
            'c2' => 'nullable|string|max:255',
            'c3' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
            'month' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
        ]);

        $item = BilanParticipation::create([
            'groupJN' => $validated['groupJN'] ?? null,
            'c$i' => $validated['c$i'] ?? null,
            'c' => $validated['c'] ?? null,
            'c1' => $validated['c1'] ?? null,
            'c2' => $validated['c2'] ?? null,
            'c3' => $validated['c3'] ?? null,
            'section' => $validated['section'] ?? null,
            'month' => $validated['month'] ?? null,
            'year' => $validated['year'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.bilan_participation.edit', $item->id)
            ->with('success', 'BilanParticipation created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = BilanParticipation::findOrFail($id);

        $validated = $request->validate([
            'groupJN' => 'nullable|string|max:255',
            'c$i' => 'nullable|string|max:255',
            'c' => 'nullable|string|max:255',
            'c1' => 'nullable|string|max:255',
            'c2' => 'nullable|string|max:255',
            'c3' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
            'month' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
        ]);

        $item->update([
            'groupJN' => $validated['groupJN'] ?? null,
            'c$i' => $validated['c$i'] ?? null,
            'c' => $validated['c'] ?? null,
            'c1' => $validated['c1'] ?? null,
            'c2' => $validated['c2'] ?? null,
            'c3' => $validated['c3'] ?? null,
            'section' => $validated['section'] ?? null,
            'month' => $validated['month'] ?? null,
            'year' => $validated['year'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.bilan_participation.edit', $item->id)
            ->with('success', 'BilanParticipation updated successfully');
    }
                

    public function destroy($id)
    {
        $item = BilanParticipation::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.bilan_participation.index')
            ->with('success', 'BilanParticipation deleted successfully');
    }
                
}
