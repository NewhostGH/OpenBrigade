<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\DownloadAddon;
use Illuminate\Http\Request;

/**
 * Legacy migration source: download_addon.php
 * Legacy pattern: generic
 * Legacy permission id: 14
 * This file stems from a legacy migration and requires functional verification.
 */
class DownloadAddonController extends Controller
{
    public function index(Request $request)
    {
        $query = DownloadAddon::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.download_addon.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.download_addon.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = DownloadAddon::findOrFail($id);

        return view('legacy_migrated.download_addon.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'module' => 'nullable|string|max:255',
            'version' => 'nullable|string|max:255',
            'md5sum' => 'nullable|string|max:255',
            'reason' => 'nullable|string|max:255',
        ]);

        $item = DownloadAddon::create([
            'module' => $validated['module'] ?? null,
            'version' => $validated['version'] ?? null,
            'md5sum' => $validated['md5sum'] ?? null,
            'reason' => $validated['reason'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.download_addon.edit', $item->id)
            ->with('success', 'DownloadAddon created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = DownloadAddon::findOrFail($id);

        $validated = $request->validate([
            'module' => 'nullable|string|max:255',
            'version' => 'nullable|string|max:255',
            'md5sum' => 'nullable|string|max:255',
            'reason' => 'nullable|string|max:255',
        ]);

        $item->update([
            'module' => $validated['module'] ?? null,
            'version' => $validated['version'] ?? null,
            'md5sum' => $validated['md5sum'] ?? null,
            'reason' => $validated['reason'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.download_addon.edit', $item->id)
            ->with('success', 'DownloadAddon updated successfully');
    }
                

    public function destroy($id)
    {
        $item = DownloadAddon::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.download_addon.index')
            ->with('success', 'DownloadAddon deleted successfully');
    }
                
}
