<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Buildzipcode;
use Illuminate\Http\Request;

/**
 * Legacy migration source: buildzipcode.php
 * Legacy pattern: generic
 * Legacy permission id: 14
 * This file stems from a legacy migration and requires functional verification.
 */
class BuildzipcodeController extends Controller
{
    public function index(Request $request)
    {
        $query = Buildzipcode::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.buildzipcode.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.buildzipcode.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Buildzipcode::findOrFail($id);

        return view('legacy_migrated.buildzipcode.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Buildzipcode::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.buildzipcode.edit', $item->id)
            ->with('success', 'Buildzipcode created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Buildzipcode::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.buildzipcode.edit', $item->id)
            ->with('success', 'Buildzipcode updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Buildzipcode::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.buildzipcode.index')
            ->with('success', 'Buildzipcode deleted successfully');
    }
                
}
