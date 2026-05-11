<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Restore;
use Illuminate\Http\Request;

/**
 * Legacy migration source: restore.php
 * Legacy pattern: generic
 * Legacy permission id: 14
 * This file stems from a legacy migration and requires functional verification.
 */
class RestoreController extends Controller
{
    public function index(Request $request)
    {
        $query = Restore::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.restore.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.restore.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Restore::findOrFail($id);

        return view('legacy_migrated.restore.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Restore::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.restore.edit', $item->id)
            ->with('success', 'Restore created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Restore::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.restore.edit', $item->id)
            ->with('success', 'Restore updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Restore::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.restore.index')
            ->with('success', 'Restore deleted successfully');
    }
                
}
