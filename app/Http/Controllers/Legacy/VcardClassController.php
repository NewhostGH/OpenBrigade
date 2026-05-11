<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\VcardClass;
use Illuminate\Http\Request;

/**
 * Legacy migration source: vcard_class.php
 * Legacy pattern: generic
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class VcardClassController extends Controller
{
    public function index(Request $request)
    {
        $query = VcardClass::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.vcard_class.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.vcard_class.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = VcardClass::findOrFail($id);

        return view('legacy_migrated.vcard_class.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = VcardClass::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.vcard_class.edit', $item->id)
            ->with('success', 'VcardClass created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = VcardClass::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.vcard_class.edit', $item->id)
            ->with('success', 'VcardClass updated successfully');
    }
                

    public function destroy($id)
    {
        $item = VcardClass::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.vcard_class.index')
            ->with('success', 'VcardClass deleted successfully');
    }
                
}
