<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\TypeEvenement;
use Illuminate\Http\Request;

/**
 * Legacy migration source: type_evenement.php
 * Legacy pattern: generic
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class TypeEvenementController extends Controller
{
    public function index(Request $request)
    {
        $query = TypeEvenement::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('te_code', 'like', '%' . $term . '%');
                $query->orWhere('te_libelle', 'like', '%' . $term . '%');
                $query->orWhere('cev_code', 'like', '%' . $term . '%');
                $query->orWhere('cev_description', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.type_evenement.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.type_evenement.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = TypeEvenement::findOrFail($id);

        return view('legacy_migrated.type_evenement.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = TypeEvenement::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.type_evenement.edit', $item->id)
            ->with('success', 'TypeEvenement created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = TypeEvenement::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.type_evenement.edit', $item->id)
            ->with('success', 'TypeEvenement updated successfully');
    }
                

    public function destroy($id)
    {
        $item = TypeEvenement::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.type_evenement.index')
            ->with('success', 'TypeEvenement deleted successfully');
    }
                
}
