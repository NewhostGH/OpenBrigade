<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Upgrade;
use Illuminate\Http\Request;

/**
 * Legacy migration source: upgrade.php
 * Legacy pattern: generic
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class UpgradeController extends Controller
{
    public function index(Request $request)
    {
        $query = Upgrade::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.upgrade.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.upgrade.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Upgrade::findOrFail($id);

        return view('legacy_migrated.upgrade.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Upgrade::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.upgrade.edit', $item->id)
            ->with('success', 'Upgrade created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Upgrade::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.upgrade.edit', $item->id)
            ->with('success', 'Upgrade updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Upgrade::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.upgrade.index')
            ->with('success', 'Upgrade deleted successfully');
    }
                
}
