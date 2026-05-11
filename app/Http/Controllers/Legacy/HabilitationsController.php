<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Habilitations;
use Illuminate\Http\Request;

/**
 * Legacy migration source: habilitations.php
 * Legacy pattern: list
 * Legacy permission id: 52
 * This file stems from a legacy migration and requires functional verification.
 */
class HabilitationsController extends Controller
{
    public function index(Request $request)
    {
        $query = Habilitations::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('tr_config', 'like', '%' . $term . '%');
                $query->orWhere('count1', 'like', '%' . $term . '%');
                $query->orWhere('f_id', 'like', '%' . $term . '%');
                $query->orWhere('f_type', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.habilitations.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.habilitations.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Habilitations::findOrFail($id);

        return view('legacy_migrated.habilitations.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'domain' => 'nullable|string|max:255',
        ]);

        $item = Habilitations::create([
            'domain' => $validated['domain'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.habilitations.edit', $item->id)
            ->with('success', 'Habilitations created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Habilitations::findOrFail($id);

        $validated = $request->validate([
            'domain' => 'nullable|string|max:255',
        ]);

        $item->update([
            'domain' => $validated['domain'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.habilitations.edit', $item->id)
            ->with('success', 'Habilitations updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Habilitations::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.habilitations.index')
            ->with('success', 'Habilitations deleted successfully');
    }
                
}
