<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Horaires;
use Illuminate\Http\Request;

/**
 * Legacy migration source: horaires.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class HorairesController extends Controller
{
    public function index(Request $request)
    {
        $query = Horaires::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('matin', 'like', '%' . $term . '%');
                $query->orWhere('aprsmidi', 'like', '%' . $term . '%');
                $query->orWhere('absence', 'like', '%' . $term . '%');
                $query->orWhere('asa', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.horaires.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.horaires.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Horaires::findOrFail($id);

        return view('legacy_migrated.horaires.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'person' => 'nullable|string|max:255',
            'week' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
            'from' => 'nullable|string|max:255',
            'debut1' => 'nullable|string|max:255',
            'fin1' => 'nullable|string|max:255',
            'debut2' => 'nullable|string|max:255',
            'fin2' => 'nullable|string|max:255',
            'asa_' => 'nullable|string|max:255',
            'forma_' => 'nullable|string|max:255',
            'formas_' => 'nullable|string|max:255',
            'duree2_' => 'nullable|string|max:255',
            'duree' => 'nullable|string|max:255',
            'total' => 'nullable|string|max:255',
            'total1' => 'nullable|string|max:255',
            'total2' => 'nullable|string|max:255',
            'menu2' => 'nullable|string|max:255',
            'menu1' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
        ]);

        $item = Horaires::create([
            'person' => $validated['person'] ?? null,
            'week' => $validated['week'] ?? null,
            'year' => $validated['year'] ?? null,
            'from' => $validated['from'] ?? null,
            'debut1' => $validated['debut1'] ?? null,
            'fin1' => $validated['fin1'] ?? null,
            'debut2' => $validated['debut2'] ?? null,
            'fin2' => $validated['fin2'] ?? null,
            'asa_' => $validated['asa_'] ?? null,
            'forma_' => $validated['forma_'] ?? null,
            'formas_' => $validated['formas_'] ?? null,
            'duree2_' => $validated['duree2_'] ?? null,
            'duree' => $validated['duree'] ?? null,
            'total' => $validated['total'] ?? null,
            'total1' => $validated['total1'] ?? null,
            'total2' => $validated['total2'] ?? null,
            'menu2' => $validated['menu2'] ?? null,
            'menu1' => $validated['menu1'] ?? null,
            'status' => $validated['status'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.horaires.edit', $item->id)
            ->with('success', 'Horaires created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Horaires::findOrFail($id);

        $validated = $request->validate([
            'person' => 'nullable|string|max:255',
            'week' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
            'from' => 'nullable|string|max:255',
            'debut1' => 'nullable|string|max:255',
            'fin1' => 'nullable|string|max:255',
            'debut2' => 'nullable|string|max:255',
            'fin2' => 'nullable|string|max:255',
            'asa_' => 'nullable|string|max:255',
            'forma_' => 'nullable|string|max:255',
            'formas_' => 'nullable|string|max:255',
            'duree2_' => 'nullable|string|max:255',
            'duree' => 'nullable|string|max:255',
            'total' => 'nullable|string|max:255',
            'total1' => 'nullable|string|max:255',
            'total2' => 'nullable|string|max:255',
            'menu2' => 'nullable|string|max:255',
            'menu1' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
        ]);

        $item->update([
            'person' => $validated['person'] ?? null,
            'week' => $validated['week'] ?? null,
            'year' => $validated['year'] ?? null,
            'from' => $validated['from'] ?? null,
            'debut1' => $validated['debut1'] ?? null,
            'fin1' => $validated['fin1'] ?? null,
            'debut2' => $validated['debut2'] ?? null,
            'fin2' => $validated['fin2'] ?? null,
            'asa_' => $validated['asa_'] ?? null,
            'forma_' => $validated['forma_'] ?? null,
            'formas_' => $validated['formas_'] ?? null,
            'duree2_' => $validated['duree2_'] ?? null,
            'duree' => $validated['duree'] ?? null,
            'total' => $validated['total'] ?? null,
            'total1' => $validated['total1'] ?? null,
            'total2' => $validated['total2'] ?? null,
            'menu2' => $validated['menu2'] ?? null,
            'menu1' => $validated['menu1'] ?? null,
            'status' => $validated['status'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.horaires.edit', $item->id)
            ->with('success', 'Horaires updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Horaires::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.horaires.index')
            ->with('success', 'Horaires deleted successfully');
    }
                
}
