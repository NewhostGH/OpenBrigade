<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\DeleteEventFile;
use Illuminate\Http\Request;

/**
 * Legacy migration source: delete_event_file.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class DeleteEventFileController extends Controller
{
    public function index(Request $request)
    {
        $query = DeleteEventFile::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('df_id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.delete_event_file.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.delete_event_file.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = DeleteEventFile::findOrFail($id);

        return view('legacy_migrated.delete_event_file.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = DeleteEventFile::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.delete_event_file.edit', $item->id)
            ->with('success', 'DeleteEventFile created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = DeleteEventFile::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.delete_event_file.edit', $item->id)
            ->with('success', 'DeleteEventFile updated successfully');
    }
                

    public function destroy($id)
    {
        $item = DeleteEventFile::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.delete_event_file.index')
            ->with('success', 'DeleteEventFile deleted successfully');
    }
                
}
