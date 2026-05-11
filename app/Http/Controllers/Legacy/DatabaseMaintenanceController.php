<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\DatabaseMaintenance;
use Illuminate\Http\Request;

/**
 * Legacy migration source: database_maintenance.php
 * Legacy pattern: generic
 * Legacy permission id: 14
 * This file stems from a legacy migration and requires functional verification.
 */
class DatabaseMaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $query = DatabaseMaintenance::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.database_maintenance.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.database_maintenance.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = DatabaseMaintenance::findOrFail($id);

        return view('legacy_migrated.database_maintenance.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = DatabaseMaintenance::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.database_maintenance.edit', $item->id)
            ->with('success', 'DatabaseMaintenance created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = DatabaseMaintenance::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.database_maintenance.edit', $item->id)
            ->with('success', 'DatabaseMaintenance updated successfully');
    }
                

    public function destroy($id)
    {
        $item = DatabaseMaintenance::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.database_maintenance.index')
            ->with('success', 'DatabaseMaintenance deleted successfully');
    }
                
}
