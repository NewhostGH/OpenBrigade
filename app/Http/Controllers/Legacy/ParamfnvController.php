<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Paramfnv;
use Illuminate\Http\Request;

/**
 * Legacy migration source: paramfnv.php
 * Legacy pattern: list
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class ParamfnvController extends Controller
{
    public function index(Request $request)
    {
        $query = Paramfnv::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('tfv_id', 'like', '%' . $term . '%');
                $query->orWhere('tfv_order', 'like', '%' . $term . '%');
                $query->orWhere('tfv_name', 'like', '%' . $term . '%');
                $query->orWhere('tfv_description', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.paramfnv.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.paramfnv.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Paramfnv::findOrFail($id);

        return view('legacy_migrated.paramfnv.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Paramfnv::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.paramfnv.edit', $item->id)
            ->with('success', 'Paramfnv created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Paramfnv::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.paramfnv.edit', $item->id)
            ->with('success', 'Paramfnv updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Paramfnv::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.paramfnv.index')
            ->with('success', 'Paramfnv deleted successfully');
    }
                
}
