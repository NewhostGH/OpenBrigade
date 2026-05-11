<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Parametrage;
use Illuminate\Http\Request;

/**
 * Legacy migration source: parametrage.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class ParametrageController extends Controller
{
    public function index(Request $request)
    {
        $query = Parametrage::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('count1', 'like', '%' . $term . '%');
                $query->orWhere('count', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.parametrage.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.parametrage.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Parametrage::findOrFail($id);

        return view('legacy_migrated.parametrage.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Parametrage::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.parametrage.edit', $item->id)
            ->with('success', 'Parametrage created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Parametrage::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.parametrage.edit', $item->id)
            ->with('success', 'Parametrage updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Parametrage::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.parametrage.index')
            ->with('success', 'Parametrage deleted successfully');
    }
                
}
