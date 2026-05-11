<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Indispo;
use Illuminate\Http\Request;

/**
 * Legacy migration source: indispo.php
 * Legacy pattern: generic
 * Legacy permission id: 11
 * This file stems from a legacy migration and requires functional verification.
 */
class IndispoController extends Controller
{
    public function index(Request $request)
    {
        $query = Indispo::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('valueclassformcontrolformcontrolsmifisset_sessionsectionordersectionorder_sessionsectionorderelsesectionorderdefaultsectionorderifcheck_rightsid', 'like', '%' . $term . '%');
                $query->orWhere('24display_children21', 'like', '%' . $term . '%');
                $query->orWhere('0', 'like', '%' . $term . '%');
                $query->orWhere('section', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.indispo.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.indispo.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Indispo::findOrFail($id);

        return view('legacy_migrated.indispo.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dc1' => 'nullable|string|max:255',
            'dc2' => 'nullable|string|max:255',
            'duree' => 'nullable|string|max:255',
            'full_day' => 'nullable|string|max:255',
            'morning' => 'nullable|string|max:255',
            'afternoon' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:255',
            's1' => 'nullable|string|max:255',
            'person' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'debut' => 'nullable|string|max:255',
            'fin' => 'nullable|string|max:255',
        ]);

        $item = Indispo::create([
            'dc1' => $validated['dc1'] ?? null,
            'dc2' => $validated['dc2'] ?? null,
            'duree' => $validated['duree'] ?? null,
            'full_day' => $validated['full_day'] ?? null,
            'morning' => $validated['morning'] ?? null,
            'afternoon' => $validated['afternoon'] ?? null,
            'comment' => $validated['comment'] ?? null,
            's1' => $validated['s1'] ?? null,
            'person' => $validated['person'] ?? null,
            'type' => $validated['type'] ?? null,
            'debut' => $validated['debut'] ?? null,
            'fin' => $validated['fin'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.indispo.edit', $item->id)
            ->with('success', 'Indispo created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Indispo::findOrFail($id);

        $validated = $request->validate([
            'dc1' => 'nullable|string|max:255',
            'dc2' => 'nullable|string|max:255',
            'duree' => 'nullable|string|max:255',
            'full_day' => 'nullable|string|max:255',
            'morning' => 'nullable|string|max:255',
            'afternoon' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:255',
            's1' => 'nullable|string|max:255',
            'person' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'debut' => 'nullable|string|max:255',
            'fin' => 'nullable|string|max:255',
        ]);

        $item->update([
            'dc1' => $validated['dc1'] ?? null,
            'dc2' => $validated['dc2'] ?? null,
            'duree' => $validated['duree'] ?? null,
            'full_day' => $validated['full_day'] ?? null,
            'morning' => $validated['morning'] ?? null,
            'afternoon' => $validated['afternoon'] ?? null,
            'comment' => $validated['comment'] ?? null,
            's1' => $validated['s1'] ?? null,
            'person' => $validated['person'] ?? null,
            'type' => $validated['type'] ?? null,
            'debut' => $validated['debut'] ?? null,
            'fin' => $validated['fin'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.indispo.edit', $item->id)
            ->with('success', 'Indispo updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Indispo::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.indispo.index')
            ->with('success', 'Indispo deleted successfully');
    }
                
}
