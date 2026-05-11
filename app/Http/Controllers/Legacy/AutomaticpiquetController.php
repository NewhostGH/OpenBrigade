<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Automaticpiquet;
use Illuminate\Http\Request;

/**
 * Legacy migration source: automaticPiquet.php
 * Legacy pattern: generic
 * Legacy permission id: 6
 * This file stems from a legacy migration and requires functional verification.
 */
class AutomaticpiquetController extends Controller
{
    public function index(Request $request)
    {
        $query = Automaticpiquet::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.automaticPiquet.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.automaticPiquet.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Automaticpiquet::findOrFail($id);

        return view('legacy_migrated.automaticPiquet.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Automaticpiquet::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.automaticPiquet.edit', $item->id)
            ->with('success', 'Automaticpiquet created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Automaticpiquet::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.automaticPiquet.edit', $item->id)
            ->with('success', 'Automaticpiquet updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Automaticpiquet::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.automaticPiquet.index')
            ->with('success', 'Automaticpiquet deleted successfully');
    }
                
}
