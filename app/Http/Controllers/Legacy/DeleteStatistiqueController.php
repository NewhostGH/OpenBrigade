<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\DeleteStatistique;
use Illuminate\Http\Request;

/**
 * Legacy migration source: delete_statistique.php
 * Legacy pattern: generic
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class DeleteStatistiqueController extends Controller
{
    public function index(Request $request)
    {
        $query = DeleteStatistique::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('te_code', 'like', '%' . $term . '%');
                $query->orWhere('tb_num', 'like', '%' . $term . '%');
                $query->orWhere('e_code', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.delete_statistique.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.delete_statistique.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = DeleteStatistique::findOrFail($id);

        return view('legacy_migrated.delete_statistique.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = DeleteStatistique::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.delete_statistique.edit', $item->id)
            ->with('success', 'DeleteStatistique created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = DeleteStatistique::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.delete_statistique.edit', $item->id)
            ->with('success', 'DeleteStatistique updated successfully');
    }
                

    public function destroy($id)
    {
        $item = DeleteStatistique::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.delete_statistique.index')
            ->with('success', 'DeleteStatistique deleted successfully');
    }
                
}
