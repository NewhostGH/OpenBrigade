<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use Illuminate\Http\Request;

/**
 * Legacy migration source: backup.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class BackupController extends Controller
{
    public function index(Request $request)
    {
        $query = Backup::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.backup.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.backup.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Backup::findOrFail($id);

        return view('legacy_migrated.backup.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Backup::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.backup.edit', $item->id)
            ->with('success', 'Backup created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Backup::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.backup.edit', $item->id)
            ->with('success', 'Backup updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Backup::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.backup.index')
            ->with('success', 'Backup deleted successfully');
    }
                
}
