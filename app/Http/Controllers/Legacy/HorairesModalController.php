<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Horaires;
use Illuminate\Http\Request;

/**
 * Legacy migration source: horaires_modal.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class HorairesModalController extends Controller
{
    public function index(Request $request)
    {
        $query = Horaires::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('count1', 'like', '%' . $term . '%');
                $query->orWhere('h_comment', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.horaires_modal.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.horaires_modal.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Horaires::findOrFail($id);

        return view('legacy_migrated.horaires_modal.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'modalcomment' => 'nullable|string|max:255',
        ]);

        $item = Horaires::create([
            'modalcomment' => $validated['modalcomment'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.horaires_modal.edit', $item->id)
            ->with('success', 'Horaires created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Horaires::findOrFail($id);

        $validated = $request->validate([
            'modalcomment' => 'nullable|string|max:255',
        ]);

        $item->update([
            'modalcomment' => $validated['modalcomment'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.horaires_modal.edit', $item->id)
            ->with('success', 'Horaires updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Horaires::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.horaires_modal.index')
            ->with('success', 'Horaires deleted successfully');
    }
                
}
