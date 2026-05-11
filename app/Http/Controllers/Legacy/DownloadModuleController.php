<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\DownloadModule;
use Illuminate\Http\Request;

/**
 * Legacy migration source: download_module.php
 * Legacy pattern: generic
 * Legacy permission id: 78
 * This file stems from a legacy migration and requires functional verification.
 */
class DownloadModuleController extends Controller
{
    public function index(Request $request)
    {
        $query = DownloadModule::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.download_module.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.download_module.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = DownloadModule::findOrFail($id);

        return view('legacy_migrated.download_module.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tab' => 'nullable|string|max:255',
        ]);

        $item = DownloadModule::create([
            'tab' => $validated['tab'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.download_module.edit', $item->id)
            ->with('success', 'DownloadModule created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = DownloadModule::findOrFail($id);

        $validated = $request->validate([
            'tab' => 'nullable|string|max:255',
        ]);

        $item->update([
            'tab' => $validated['tab'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.download_module.edit', $item->id)
            ->with('success', 'DownloadModule updated successfully');
    }
                

    public function destroy($id)
    {
        $item = DownloadModule::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.download_module.index')
            ->with('success', 'DownloadModule deleted successfully');
    }
                
}
