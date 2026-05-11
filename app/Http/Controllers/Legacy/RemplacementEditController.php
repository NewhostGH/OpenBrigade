<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\RemplacementEdit;
use Illuminate\Http\Request;

/**
 * Legacy migration source: remplacement_edit.php
 * Legacy pattern: list
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class RemplacementEditController extends Controller
{
    public function index(Request $request)
    {
        $query = RemplacementEdit::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('replaced', 'like', '%' . $term . '%');
                $query->orWhere('substitute', 'like', '%' . $term . '%');
                $query->orWhere('accepted', 'like', '%' . $term . '%');
                $query->orWhere('approved', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.remplacement_edit.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.remplacement_edit.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = RemplacementEdit::findOrFail($id);

        return view('legacy_migrated.remplacement_edit.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'replaced' => 'nullable|string|max:255',
            'substitute' => 'nullable|string|max:255',
        ]);

        $item = RemplacementEdit::create([
            'replaced' => $validated['replaced'] ?? null,
            'substitute' => $validated['substitute'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.remplacement_edit.edit', $item->id)
            ->with('success', 'RemplacementEdit created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = RemplacementEdit::findOrFail($id);

        $validated = $request->validate([
            'replaced' => 'nullable|string|max:255',
            'substitute' => 'nullable|string|max:255',
        ]);

        $item->update([
            'replaced' => $validated['replaced'] ?? null,
            'substitute' => $validated['substitute'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.remplacement_edit.edit', $item->id)
            ->with('success', 'RemplacementEdit updated successfully');
    }
                

    public function destroy($id)
    {
        $item = RemplacementEdit::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.remplacement_edit.index')
            ->with('success', 'RemplacementEdit deleted successfully');
    }
                
}
