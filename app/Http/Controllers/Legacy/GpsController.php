<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Gps;
use Illuminate\Http\Request;

/**
 * Legacy migration source: gps.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class GpsController extends Controller
{
    public function index(Request $request)
    {
        $query = Gps::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_id', 'like', '%' . $term . '%');
                $query->orWhere('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
                $query->orWhere('p_sexe', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.gps.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.gps.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Gps::findOrFail($id);

        return view('legacy_migrated.gps.form', [
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

        $item = Gps::create([
            'sub' => $validated['sub'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'competence' => $validated['competence'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.gps.edit', $item->id)
            ->with('success', 'Gps created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Gps::findOrFail($id);

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

        return redirect()->route('legacy_migrated.gps.edit', $item->id)
            ->with('success', 'Gps updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Gps::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.gps.index')
            ->with('success', 'Gps deleted successfully');
    }
                
}
