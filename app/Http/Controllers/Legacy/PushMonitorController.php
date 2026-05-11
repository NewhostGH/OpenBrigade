<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\PushMonitor;
use Illuminate\Http\Request;

/**
 * Legacy migration source: push_monitor.php
 * Legacy pattern: generic
 * Legacy permission id: 14
 * This file stems from a legacy migration and requires functional verification.
 */
class PushMonitorController extends Controller
{
    public function index(Request $request)
    {
        $query = PushMonitor::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.push_monitor.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.push_monitor.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = PushMonitor::findOrFail($id);

        return view('legacy_migrated.push_monitor.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = PushMonitor::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.push_monitor.edit', $item->id)
            ->with('success', 'PushMonitor created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = PushMonitor::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.push_monitor.edit', $item->id)
            ->with('success', 'PushMonitor updated successfully');
    }
                

    public function destroy($id)
    {
        $item = PushMonitor::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.push_monitor.index')
            ->with('success', 'PushMonitor deleted successfully');
    }
                
}
