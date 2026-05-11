<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Vcard;
use Illuminate\Http\Request;

/**
 * Legacy migration source: vcard.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class VcardController extends Controller
{
    public function index(Request $request)
    {
        $query = Vcard::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('select', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.vcard.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.vcard.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Vcard::findOrFail($id);

        return view('legacy_migrated.vcard.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Vcard::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.vcard.edit', $item->id)
            ->with('success', 'Vcard created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Vcard::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.vcard.edit', $item->id)
            ->with('success', 'Vcard updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Vcard::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.vcard.index')
            ->with('success', 'Vcard deleted successfully');
    }
                
}
