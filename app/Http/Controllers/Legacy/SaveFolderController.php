<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_folder.php
 * Legacy pattern: save
 * Legacy permission id: 47
 * This file stems from a legacy migration and requires functional verification.
 */
class SaveFolderController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_folder.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Folder::findOrFail($id);

        return view('legacy_migrated.save_folder.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'S_ID' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'dossier_parent' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'folder' => 'nullable|string|max:255',
        ]);

        $item = Folder::create([
            'S_ID' => $validated['S_ID'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'dossier_parent' => $validated['dossier_parent'] ?? null,
            'type' => $validated['type'] ?? null,
            'folder' => $validated['folder'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_folder.edit', $item->id)
            ->with('success', 'Folder created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Folder::findOrFail($id);

        $validated = $request->validate([
            'S_ID' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'dossier_parent' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'folder' => 'nullable|string|max:255',
        ]);

        $item->update([
            'S_ID' => $validated['S_ID'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'dossier_parent' => $validated['dossier_parent'] ?? null,
            'type' => $validated['type'] ?? null,
            'folder' => $validated['folder'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_folder.edit', $item->id)
            ->with('success', 'Folder updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Folder::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_folder.index')
            ->with('success', 'Folder deleted successfully');
    }
                

    public function applyOperation(Request $request)
    {
        $operation = (string) $request->input('operation');

        if ($operation === 'insert') {
            return response()->json(['status' => 'ok', 'operation' => 'insert']);
        }

        return response()->json(['status' => 'ignored', 'operation' => $operation]);
    }
}
