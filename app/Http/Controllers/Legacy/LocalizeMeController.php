<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\LocalizeMe;
use Illuminate\Http\Request;

/**
 * Legacy migration source: localize_me.php
 * Legacy pattern: generic
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class LocalizeMeController extends Controller
{
    public function index(Request $request)
    {
        $query = LocalizeMe::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('d_secret', 'like', '%' . $term . '%');
                $query->orWhere('d_by', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.localize_me.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.localize_me.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = LocalizeMe::findOrFail($id);

        return view('legacy_migrated.localize_me.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = LocalizeMe::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.localize_me.edit', $item->id)
            ->with('success', 'LocalizeMe created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = LocalizeMe::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.localize_me.edit', $item->id)
            ->with('success', 'LocalizeMe updated successfully');
    }
                

    public function destroy($id)
    {
        $item = LocalizeMe::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.localize_me.index')
            ->with('success', 'LocalizeMe deleted successfully');
    }
                
}
