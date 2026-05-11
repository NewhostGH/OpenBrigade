<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Upload;
use Illuminate\Http\Request;

/**
 * Legacy migration source: upload.php
 * Legacy pattern: generic
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class UploadController extends Controller
{
    public function index(Request $request)
    {
        $query = Upload::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.upload.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.upload.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Upload::findOrFail($id);

        return view('legacy_migrated.upload.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'operation' => 'nullable|string|max:255',
            'old' => 'nullable|string|max:255',
        ]);

        $item = Upload::create([
            'operation' => $validated['operation'] ?? null,
            'old' => $validated['old'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upload.edit', $item->id)
            ->with('success', 'Upload created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Upload::findOrFail($id);

        $validated = $request->validate([
            'operation' => 'nullable|string|max:255',
            'old' => 'nullable|string|max:255',
        ]);

        $item->update([
            'operation' => $validated['operation'] ?? null,
            'old' => $validated['old'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upload.edit', $item->id)
            ->with('success', 'Upload updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Upload::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.upload.index')
            ->with('success', 'Upload deleted successfully');
    }
                

    public function applyOperation(Request $request)
    {
        $operation = (string) $request->input('operation');

        if ($operation === 'insert') {
            return response()->json(['status' => 'ok', 'operation' => 'insert']);
        }

        if ($operation === 'update') {
            return response()->json(['status' => 'ok', 'operation' => 'update']);
        }

        return response()->json(['status' => 'ignored', 'operation' => $operation]);
    }
}
