<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Showfile;
use Illuminate\Http\Request;

/**
 * Legacy migration source: showfile.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class ShowfileController extends Controller
{
    public function index(Request $request)
    {
        $query = Showfile::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('distincts_idexternal_section', 'like', '%' . $term . '%');
                $query->orWhere('f_id', 'like', '%' . $term . '%');
                $query->orWhere('d_created_by', 'like', '%' . $term . '%');
                $query->orWhere('td_security', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.showfile.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.showfile.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Showfile::findOrFail($id);

        return view('legacy_migrated.showfile.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Showfile::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.showfile.edit', $item->id)
            ->with('success', 'Showfile created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Showfile::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.showfile.edit', $item->id)
            ->with('success', 'Showfile updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Showfile::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.showfile.index')
            ->with('success', 'Showfile deleted successfully');
    }
                
}
