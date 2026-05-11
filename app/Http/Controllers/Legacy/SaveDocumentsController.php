<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Documents;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_documents.php
 * Legacy pattern: save
 * Legacy permission id: 47
 * This file stems from a legacy migration and requires functional verification.
 */
class SaveDocumentsController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_documents.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Documents::findOrFail($id);

        return view('legacy_migrated.save_documents.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'S_ID' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:255',
            'security' => 'nullable|string|max:255',
            'dossier' => 'nullable|string|max:255',
            'from' => 'nullable|string|max:255',
            'vehicule' => 'nullable|string|max:255',
            'materiel' => 'nullable|string|max:255',
            'docid' => 'nullable|string|max:255',
            'isfolder' => 'nullable|string|max:255',
            'parentfolder' => 'nullable|string|max:255',
            'foldername' => 'nullable|string|max:255',
        ]);

        $item = Documents::create([
            'S_ID' => $validated['S_ID'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'type' => $validated['type'] ?? null,
            'url' => $validated['url'] ?? null,
            'security' => $validated['security'] ?? null,
            'dossier' => $validated['dossier'] ?? null,
            'from' => $validated['from'] ?? null,
            'vehicule' => $validated['vehicule'] ?? null,
            'materiel' => $validated['materiel'] ?? null,
            'docid' => $validated['docid'] ?? null,
            'isfolder' => $validated['isfolder'] ?? null,
            'parentfolder' => $validated['parentfolder'] ?? null,
            'foldername' => $validated['foldername'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_documents.edit', $item->id)
            ->with('success', 'Documents created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Documents::findOrFail($id);

        $validated = $request->validate([
            'S_ID' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:255',
            'security' => 'nullable|string|max:255',
            'dossier' => 'nullable|string|max:255',
            'from' => 'nullable|string|max:255',
            'vehicule' => 'nullable|string|max:255',
            'materiel' => 'nullable|string|max:255',
            'docid' => 'nullable|string|max:255',
            'isfolder' => 'nullable|string|max:255',
            'parentfolder' => 'nullable|string|max:255',
            'foldername' => 'nullable|string|max:255',
        ]);

        $item->update([
            'S_ID' => $validated['S_ID'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'type' => $validated['type'] ?? null,
            'url' => $validated['url'] ?? null,
            'security' => $validated['security'] ?? null,
            'dossier' => $validated['dossier'] ?? null,
            'from' => $validated['from'] ?? null,
            'vehicule' => $validated['vehicule'] ?? null,
            'materiel' => $validated['materiel'] ?? null,
            'docid' => $validated['docid'] ?? null,
            'isfolder' => $validated['isfolder'] ?? null,
            'parentfolder' => $validated['parentfolder'] ?? null,
            'foldername' => $validated['foldername'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_documents.edit', $item->id)
            ->with('success', 'Documents updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Documents::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_documents.index')
            ->with('success', 'Documents deleted successfully');
    }
                

    public function applyOperation(Request $request)
    {
        $operation = (string) $request->input('operation');

        if ($operation === 'updatedoc') {
            return response()->json(['status' => 'ok', 'operation' => 'updatedoc']);
        }

        return response()->json(['status' => 'ignored', 'operation' => $operation]);
    }
}
