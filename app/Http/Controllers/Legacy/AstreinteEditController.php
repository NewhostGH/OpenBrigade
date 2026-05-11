<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\AstreinteEdit;
use Illuminate\Http\Request;

/**
 * Legacy migration source: astreinte_edit.php
 * Legacy pattern: list
 * Legacy permission id: 26
 * This file stems from a legacy migration and requires functional verification.
 */
class AstreinteEditController extends Controller
{
    public function index(Request $request)
    {
        $query = AstreinteEdit::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('as_id', 'like', '%' . $term . '%');
                $query->orWhere('s_id', 'like', '%' . $term . '%');
                $query->orWhere('gp_id', 'like', '%' . $term . '%');
                $query->orWhere('p_id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.astreinte_edit.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.astreinte_edit.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = AstreinteEdit::findOrFail($id);

        return view('legacy_migrated.astreinte_edit.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'astreinte' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'dc1' => 'nullable|string|max:255',
            'dc2' => 'nullable|string|max:255',
            'person' => 'nullable|string|max:255',
        ]);

        $item = AstreinteEdit::create([
            'astreinte' => $validated['astreinte'] ?? null,
            'section' => $validated['section'] ?? null,
            'type' => $validated['type'] ?? null,
            'dc1' => $validated['dc1'] ?? null,
            'dc2' => $validated['dc2'] ?? null,
            'person' => $validated['person'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.astreinte_edit.edit', $item->id)
            ->with('success', 'AstreinteEdit created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = AstreinteEdit::findOrFail($id);

        $validated = $request->validate([
            'astreinte' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'dc1' => 'nullable|string|max:255',
            'dc2' => 'nullable|string|max:255',
            'person' => 'nullable|string|max:255',
        ]);

        $item->update([
            'astreinte' => $validated['astreinte'] ?? null,
            'section' => $validated['section'] ?? null,
            'type' => $validated['type'] ?? null,
            'dc1' => $validated['dc1'] ?? null,
            'dc2' => $validated['dc2'] ?? null,
            'person' => $validated['person'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.astreinte_edit.edit', $item->id)
            ->with('success', 'AstreinteEdit updated successfully');
    }
                

    public function destroy($id)
    {
        $item = AstreinteEdit::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.astreinte_edit.index')
            ->with('success', 'AstreinteEdit deleted successfully');
    }
                
}
