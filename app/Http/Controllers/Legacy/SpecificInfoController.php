<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\SpecificInfo;
use Illuminate\Http\Request;

/**
 * Legacy migration source: specific_info.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class SpecificInfoController extends Controller
{
    public function index(Request $request)
    {
        $query = SpecificInfo::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.specific_info.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.specific_info.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = SpecificInfo::findOrFail($id);

        return view('legacy_migrated.specific_info.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = SpecificInfo::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.specific_info.edit', $item->id)
            ->with('success', 'SpecificInfo created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = SpecificInfo::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.specific_info.edit', $item->id)
            ->with('success', 'SpecificInfo updated successfully');
    }
                

    public function destroy($id)
    {
        $item = SpecificInfo::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.specific_info.index')
            ->with('success', 'SpecificInfo deleted successfully');
    }
                
}
