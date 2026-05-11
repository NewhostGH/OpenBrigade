<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Myagenda;
use Illuminate\Http\Request;

/**
 * Legacy migration source: myagenda.php
 * Legacy pattern: list
 * Legacy permission id: 41
 * This file stems from a legacy migration and requires functional verification.
 */
class MyagendaController extends Controller
{
    public function index(Request $request)
    {
        $query = Myagenda::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_id', 'like', '%' . $term . '%');
                $query->orWhere('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_mdppkey', 'like', '%' . $term . '%');
                $query->orWhere('p_calendar', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.myagenda.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.myagenda.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Myagenda::findOrFail($id);

        return view('legacy_migrated.myagenda.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Myagenda::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.myagenda.edit', $item->id)
            ->with('success', 'Myagenda created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Myagenda::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.myagenda.edit', $item->id)
            ->with('success', 'Myagenda updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Myagenda::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.myagenda.index')
            ->with('success', 'Myagenda deleted successfully');
    }
                
}
