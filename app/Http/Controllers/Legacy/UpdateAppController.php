<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\UpdateApp;
use Illuminate\Http\Request;

/**
 * Legacy migration source: update_app.php
 * Legacy pattern: generic
 * Legacy permission id: 14
 * This file stems from a legacy migration and requires functional verification.
 */
class UpdateAppController extends Controller
{
    public function index(Request $request)
    {
        $query = UpdateApp::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('value', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.update_app.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.update_app.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = UpdateApp::findOrFail($id);

        return view('legacy_migrated.update_app.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'package' => 'nullable|string|max:255',
            'reason' => 'nullable|string|max:255',
            'patch_version' => 'nullable|string|max:255',
        ]);

        $item = UpdateApp::create([
            'package' => $validated['package'] ?? null,
            'reason' => $validated['reason'] ?? null,
            'patch_version' => $validated['patch_version'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.update_app.edit', $item->id)
            ->with('success', 'UpdateApp created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = UpdateApp::findOrFail($id);

        $validated = $request->validate([
            'package' => 'nullable|string|max:255',
            'reason' => 'nullable|string|max:255',
            'patch_version' => 'nullable|string|max:255',
        ]);

        $item->update([
            'package' => $validated['package'] ?? null,
            'reason' => $validated['reason'] ?? null,
            'patch_version' => $validated['patch_version'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.update_app.edit', $item->id)
            ->with('success', 'UpdateApp updated successfully');
    }
                

    public function destroy($id)
    {
        $item = UpdateApp::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.update_app.index')
            ->with('success', 'UpdateApp deleted successfully');
    }
                
}
