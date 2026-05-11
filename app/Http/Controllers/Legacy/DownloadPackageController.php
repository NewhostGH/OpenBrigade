<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\DownloadPackage;
use Illuminate\Http\Request;

/**
 * Legacy migration source: download_package.php
 * Legacy pattern: generic
 * Legacy permission id: 14
 * This file stems from a legacy migration and requires functional verification.
 */
class DownloadPackageController extends Controller
{
    public function index(Request $request)
    {
        $query = DownloadPackage::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.download_package.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.download_package.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = DownloadPackage::findOrFail($id);

        return view('legacy_migrated.download_package.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'package' => 'nullable|string|max:255',
            'md5sum' => 'nullable|string|max:255',
            'reason' => 'nullable|string|max:255',
        ]);

        $item = DownloadPackage::create([
            'package' => $validated['package'] ?? null,
            'md5sum' => $validated['md5sum'] ?? null,
            'reason' => $validated['reason'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.download_package.edit', $item->id)
            ->with('success', 'DownloadPackage created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = DownloadPackage::findOrFail($id);

        $validated = $request->validate([
            'package' => 'nullable|string|max:255',
            'md5sum' => 'nullable|string|max:255',
            'reason' => 'nullable|string|max:255',
        ]);

        $item->update([
            'package' => $validated['package'] ?? null,
            'md5sum' => $validated['md5sum'] ?? null,
            'reason' => $validated['reason'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.download_package.edit', $item->id)
            ->with('success', 'DownloadPackage updated successfully');
    }
                

    public function destroy($id)
    {
        $item = DownloadPackage::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.download_package.index')
            ->with('success', 'DownloadPackage deleted successfully');
    }
                
}
