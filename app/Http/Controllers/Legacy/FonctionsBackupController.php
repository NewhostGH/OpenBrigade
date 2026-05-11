<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\FonctionsBackup;
use Illuminate\Http\Request;

/**
 * Legacy migration source: fonctions_backup.php
 * Legacy pattern: list
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class FonctionsBackupController extends Controller
{
    public function index(Request $request)
    {
        $query = FonctionsBackup::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.fonctions_backup.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.fonctions_backup.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = FonctionsBackup::findOrFail($id);

        return view('legacy_migrated.fonctions_backup.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = FonctionsBackup::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.fonctions_backup.edit', $item->id)
            ->with('success', 'FonctionsBackup created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = FonctionsBackup::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.fonctions_backup.edit', $item->id)
            ->with('success', 'FonctionsBackup updated successfully');
    }
                

    public function destroy($id)
    {
        $item = FonctionsBackup::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.fonctions_backup.index')
            ->with('success', 'FonctionsBackup deleted successfully');
    }
                
}
