<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\SectionStop;
use Illuminate\Http\Request;

/**
 * Legacy migration source: section_stop.php
 * Legacy pattern: generic
 * Legacy permission id: 22
 * This file stems from a legacy migration and requires functional verification.
 */
class SectionStopController extends Controller
{
    public function index(Request $request)
    {
        $query = SectionStop::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('s_id', 'like', '%' . $term . '%');
                $query->orWhere('sse_id', 'like', '%' . $term . '%');
                $query->orWhere('te_code', 'like', '%' . $term . '%');
                $query->orWhere('start_date', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.section_stop.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.section_stop.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = SectionStop::findOrFail($id);

        return view('legacy_migrated.section_stop.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'section' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'sseid' => 'nullable|string|max:255',
            'start' => 'nullable|string|max:255',
            'end' => 'nullable|string|max:255',
            'active' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
        ]);

        $item = SectionStop::create([
            'section' => $validated['section'] ?? null,
            'action' => $validated['action'] ?? null,
            'sseid' => $validated['sseid'] ?? null,
            'start' => $validated['start'] ?? null,
            'end' => $validated['end'] ?? null,
            'active' => $validated['active'] ?? null,
            'comment' => $validated['comment'] ?? null,
            'type' => $validated['type'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.section_stop.edit', $item->id)
            ->with('success', 'SectionStop created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = SectionStop::findOrFail($id);

        $validated = $request->validate([
            'section' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'sseid' => 'nullable|string|max:255',
            'start' => 'nullable|string|max:255',
            'end' => 'nullable|string|max:255',
            'active' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
        ]);

        $item->update([
            'section' => $validated['section'] ?? null,
            'action' => $validated['action'] ?? null,
            'sseid' => $validated['sseid'] ?? null,
            'start' => $validated['start'] ?? null,
            'end' => $validated['end'] ?? null,
            'active' => $validated['active'] ?? null,
            'comment' => $validated['comment'] ?? null,
            'type' => $validated['type'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.section_stop.edit', $item->id)
            ->with('success', 'SectionStop updated successfully');
    }
                

    public function destroy($id)
    {
        $item = SectionStop::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.section_stop.index')
            ->with('success', 'SectionStop deleted successfully');
    }
                
}
