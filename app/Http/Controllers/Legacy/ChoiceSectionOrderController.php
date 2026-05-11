<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\ChoiceSectionOrder;
use Illuminate\Http\Request;

/**
 * Legacy migration source: choice_section_order.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class ChoiceSectionOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = ChoiceSectionOrder::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.choice_section_order.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.choice_section_order.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = ChoiceSectionOrder::findOrFail($id);

        return view('legacy_migrated.choice_section_order.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'radio' => 'nullable|string|max:255',
        ]);

        $item = ChoiceSectionOrder::create([
            'radio' => $validated['radio'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.choice_section_order.edit', $item->id)
            ->with('success', 'ChoiceSectionOrder created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = ChoiceSectionOrder::findOrFail($id);

        $validated = $request->validate([
            'radio' => 'nullable|string|max:255',
        ]);

        $item->update([
            'radio' => $validated['radio'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.choice_section_order.edit', $item->id)
            ->with('success', 'ChoiceSectionOrder updated successfully');
    }
                

    public function destroy($id)
    {
        $item = ChoiceSectionOrder::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.choice_section_order.index')
            ->with('success', 'ChoiceSectionOrder deleted successfully');
    }
                
}
