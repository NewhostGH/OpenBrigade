<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Poste;
use Illuminate\Http\Request;

/**
 * Legacy migration source: poste.php
 * Legacy pattern: generic
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class PosteController extends Controller
{
    public function index(Request $request)
    {
        $query = Poste::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('ps_id', 'like', '%' . $term . '%');
                $query->orWhere('eq_id', 'like', '%' . $term . '%');
                $query->orWhere('type', 'like', '%' . $term . '%');
                $query->orWhere('description', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.poste.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.poste.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Poste::findOrFail($id);

        return view('legacy_migrated.poste.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'typequalif' => 'nullable|string|max:255',
        ]);

        $item = Poste::create([
            'typequalif' => $validated['typequalif'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.poste.edit', $item->id)
            ->with('success', 'Poste created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Poste::findOrFail($id);

        $validated = $request->validate([
            'typequalif' => 'nullable|string|max:255',
        ]);

        $item->update([
            'typequalif' => $validated['typequalif'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.poste.edit', $item->id)
            ->with('success', 'Poste updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Poste::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.poste.index')
            ->with('success', 'Poste deleted successfully');
    }
                
}
