<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Zipcode;
use Illuminate\Http\Request;

/**
 * Legacy migration source: zipcode.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class ZipcodeController extends Controller
{
    public function index(Request $request)
    {
        $query = Zipcode::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('code', 'like', '%' . $term . '%');
                $query->orWhere('city', 'like', '%' . $term . '%');
                $query->orWhere('dep', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.zipcode.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.zipcode.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Zipcode::findOrFail($id);

        return view('legacy_migrated.zipcode.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'maxRows' => 'nullable|string|max:255',
            'ZipCode' => 'nullable|string|max:255',
            'City' => 'nullable|string|max:255',
        ]);

        $item = Zipcode::create([
            'maxRows' => $validated['maxRows'] ?? null,
            'ZipCode' => $validated['ZipCode'] ?? null,
            'City' => $validated['City'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.zipcode.edit', $item->id)
            ->with('success', 'Zipcode created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Zipcode::findOrFail($id);

        $validated = $request->validate([
            'maxRows' => 'nullable|string|max:255',
            'ZipCode' => 'nullable|string|max:255',
            'City' => 'nullable|string|max:255',
        ]);

        $item->update([
            'maxRows' => $validated['maxRows'] ?? null,
            'ZipCode' => $validated['ZipCode'] ?? null,
            'City' => $validated['City'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.zipcode.edit', $item->id)
            ->with('success', 'Zipcode updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Zipcode::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.zipcode.index')
            ->with('success', 'Zipcode deleted successfully');
    }
                
}
