<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\VirementsExtract;
use Illuminate\Http\Request;

/**
 * Legacy migration source: virements_extract.php
 * Legacy pattern: list
 * Legacy permission id: 53
 * This file stems from a legacy migration and requires functional verification.
 */
class VirementsExtractController extends Controller
{
    public function index(Request $request)
    {
        $query = VirementsExtract::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_id', 'like', '%' . $term . '%');
                $query->orWhere('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
                $query->orWhere('pc_id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.virements_extract.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.virements_extract.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = VirementsExtract::findOrFail($id);

        return view('legacy_migrated.virements_extract.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = VirementsExtract::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.virements_extract.edit', $item->id)
            ->with('success', 'VirementsExtract created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = VirementsExtract::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.virements_extract.edit', $item->id)
            ->with('success', 'VirementsExtract updated successfully');
    }
                

    public function destroy($id)
    {
        $item = VirementsExtract::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.virements_extract.index')
            ->with('success', 'VirementsExtract deleted successfully');
    }
                
}
