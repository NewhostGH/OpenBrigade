<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\TypeConsommable;
use Illuminate\Http\Request;

/**
 * Legacy migration source: type_consommable.php
 * Legacy pattern: list
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class TypeConsommableController extends Controller
{
    public function index(Request $request)
    {
        $query = TypeConsommable::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('cc_code', 'like', '%' . $term . '%');
                $query->orWhere('cc_name', 'like', '%' . $term . '%');
                $query->orWhere('cc_description', 'like', '%' . $term . '%');
                $query->orWhere('cc_image', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.type_consommable.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.type_consommable.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = TypeConsommable::findOrFail($id);

        return view('legacy_migrated.type_consommable.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'usage' => 'nullable|string|max:255',
        ]);

        $item = TypeConsommable::create([
            'usage' => $validated['usage'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.type_consommable.edit', $item->id)
            ->with('success', 'TypeConsommable created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = TypeConsommable::findOrFail($id);

        $validated = $request->validate([
            'usage' => 'nullable|string|max:255',
        ]);

        $item->update([
            'usage' => $validated['usage'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.type_consommable.edit', $item->id)
            ->with('success', 'TypeConsommable updated successfully');
    }
                

    public function destroy($id)
    {
        $item = TypeConsommable::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.type_consommable.index')
            ->with('success', 'TypeConsommable deleted successfully');
    }
                
}
