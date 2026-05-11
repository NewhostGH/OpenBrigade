<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\AutoGarde;
use Illuminate\Http\Request;

/**
 * Legacy migration source: auto_garde.php
 * Legacy pattern: generic
 * Legacy permission id: 6
 * This file stems from a legacy migration and requires functional verification.
 */
class AutoGardeController extends Controller
{
    public function index(Request $request)
    {
        $query = AutoGarde::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.auto_garde.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.auto_garde.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = AutoGarde::findOrFail($id);

        return view('legacy_migrated.auto_garde.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = AutoGarde::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.auto_garde.edit', $item->id)
            ->with('success', 'AutoGarde created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = AutoGarde::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.auto_garde.edit', $item->id)
            ->with('success', 'AutoGarde updated successfully');
    }
                

    public function destroy($id)
    {
        $item = AutoGarde::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.auto_garde.index')
            ->with('success', 'AutoGarde deleted successfully');
    }
                
}
