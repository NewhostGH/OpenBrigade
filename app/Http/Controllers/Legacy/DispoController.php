<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Dispo;
use Illuminate\Http\Request;

/**
 * Legacy migration source: dispo.php
 * Legacy pattern: list
 * Legacy permission id: 38
 * This file stems from a legacy migration and requires functional verification.
 */
class DispoController extends Controller
{
    public function index(Request $request)
    {
        $query = Dispo::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('count1', 'like', '%' . $term . '%');
                $query->orWhere('form', 'like', '%' . $term . '%');
                $query->orWhere('menu1', 'like', '%' . $term . '%');
                $query->orWhere('menu2', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.dispo.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.dispo.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Dispo::findOrFail($id);

        return view('legacy_migrated.dispo.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ouvrir' => 'nullable|string|max:255',
            'fermer' => 'nullable|string|max:255',
            'CheckAll' => 'nullable|string|max:255',
            'nbjours' => 'nullable|string|max:255',
            'person' => 'nullable|string|max:255',
            'month' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
            '1_' => 'nullable|string|max:255',
            '4_' => 'nullable|string|max:255',
            '2_' => 'nullable|string|max:255',
            '3_' => 'nullable|string|max:255',
            'msg' => 'nullable|string|max:255',
            'menu2' => 'nullable|string|max:255',
            'menu1' => 'nullable|string|max:255',
            'filtre' => 'nullable|string|max:255',
            'menu4' => 'nullable|string|max:255',
            'menu5' => 'nullable|string|max:255',
            'menu3' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
        ]);

        $item = Dispo::create([
            'ouvrir' => $validated['ouvrir'] ?? null,
            'fermer' => $validated['fermer'] ?? null,
            'CheckAll' => $validated['CheckAll'] ?? null,
            'nbjours' => $validated['nbjours'] ?? null,
            'person' => $validated['person'] ?? null,
            'month' => $validated['month'] ?? null,
            'year' => $validated['year'] ?? null,
            '1_' => $validated['1_'] ?? null,
            '4_' => $validated['4_'] ?? null,
            '2_' => $validated['2_'] ?? null,
            '3_' => $validated['3_'] ?? null,
            'msg' => $validated['msg'] ?? null,
            'menu2' => $validated['menu2'] ?? null,
            'menu1' => $validated['menu1'] ?? null,
            'filtre' => $validated['filtre'] ?? null,
            'menu4' => $validated['menu4'] ?? null,
            'menu5' => $validated['menu5'] ?? null,
            'menu3' => $validated['menu3'] ?? null,
            'section' => $validated['section'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.dispo.edit', $item->id)
            ->with('success', 'Dispo created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Dispo::findOrFail($id);

        $validated = $request->validate([
            'ouvrir' => 'nullable|string|max:255',
            'fermer' => 'nullable|string|max:255',
            'CheckAll' => 'nullable|string|max:255',
            'nbjours' => 'nullable|string|max:255',
            'person' => 'nullable|string|max:255',
            'month' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
            '1_' => 'nullable|string|max:255',
            '4_' => 'nullable|string|max:255',
            '2_' => 'nullable|string|max:255',
            '3_' => 'nullable|string|max:255',
            'msg' => 'nullable|string|max:255',
            'menu2' => 'nullable|string|max:255',
            'menu1' => 'nullable|string|max:255',
            'filtre' => 'nullable|string|max:255',
            'menu4' => 'nullable|string|max:255',
            'menu5' => 'nullable|string|max:255',
            'menu3' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
        ]);

        $item->update([
            'ouvrir' => $validated['ouvrir'] ?? null,
            'fermer' => $validated['fermer'] ?? null,
            'CheckAll' => $validated['CheckAll'] ?? null,
            'nbjours' => $validated['nbjours'] ?? null,
            'person' => $validated['person'] ?? null,
            'month' => $validated['month'] ?? null,
            'year' => $validated['year'] ?? null,
            '1_' => $validated['1_'] ?? null,
            '4_' => $validated['4_'] ?? null,
            '2_' => $validated['2_'] ?? null,
            '3_' => $validated['3_'] ?? null,
            'msg' => $validated['msg'] ?? null,
            'menu2' => $validated['menu2'] ?? null,
            'menu1' => $validated['menu1'] ?? null,
            'filtre' => $validated['filtre'] ?? null,
            'menu4' => $validated['menu4'] ?? null,
            'menu5' => $validated['menu5'] ?? null,
            'menu3' => $validated['menu3'] ?? null,
            'section' => $validated['section'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.dispo.edit', $item->id)
            ->with('success', 'Dispo updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Dispo::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.dispo.index')
            ->with('success', 'Dispo deleted successfully');
    }
                
}
