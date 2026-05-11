<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\DeleteFile;
use Illuminate\Http\Request;

/**
 * Legacy migration source: delete_file.php
 * Legacy pattern: generic
 * Legacy permission id: 14
 * This file stems from a legacy migration and requires functional verification.
 */
class DeleteFileController extends Controller
{
    public function index(Request $request)
    {
        $query = DeleteFile::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.delete_file.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.delete_file.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = DeleteFile::findOrFail($id);

        return view('legacy_migrated.delete_file.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = DeleteFile::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.delete_file.edit', $item->id)
            ->with('success', 'DeleteFile created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = DeleteFile::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.delete_file.edit', $item->id)
            ->with('success', 'DeleteFile updated successfully');
    }
                

    public function destroy($id)
    {
        $item = DeleteFile::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.delete_file.index')
            ->with('success', 'DeleteFile deleted successfully');
    }
                
}
