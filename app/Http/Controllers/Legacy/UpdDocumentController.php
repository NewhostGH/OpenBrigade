<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;

/**
 * Legacy migration source: upd_document.php
 * Legacy pattern: edit
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class UpdDocumentController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.upd_document.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Document::findOrFail($id);

        return view('legacy_migrated.upd_document.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'operation' => 'nullable|string|max:255',
            'S_ID' => 'nullable|string|max:255',
            'P_ID' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
            'victime' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'numinter' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'modeinter' => 'nullable|string|max:255',
            'vehicule' => 'nullable|string|max:255',
            'materiel' => 'nullable|string|max:255',
            'person' => 'nullable|string|max:255',
            'nfid' => 'nullable|string|max:255',
            'dossier' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'security' => 'nullable|string|max:255',
            'from' => 'nullable|string|max:255',
            'userfile[]' => 'nullable|file',
        ]);

        $item = Document::create([
            'operation' => $validated['operation'] ?? null,
            'S_ID' => $validated['S_ID'] ?? null,
            'P_ID' => $validated['P_ID'] ?? null,
            'status' => $validated['status'] ?? null,
            'section' => $validated['section'] ?? null,
            'victime' => $validated['victime'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'numinter' => $validated['numinter'] ?? null,
            'action' => $validated['action'] ?? null,
            'modeinter' => $validated['modeinter'] ?? null,
            'vehicule' => $validated['vehicule'] ?? null,
            'materiel' => $validated['materiel'] ?? null,
            'person' => $validated['person'] ?? null,
            'nfid' => $validated['nfid'] ?? null,
            'dossier' => $validated['dossier'] ?? null,
            'type' => $validated['type'] ?? null,
            'security' => $validated['security'] ?? null,
            'from' => $validated['from'] ?? null,
            'userfile[]' => $validated['userfile[]'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_document.edit', $item->id)
            ->with('success', 'Document created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Document::findOrFail($id);

        $validated = $request->validate([
            'operation' => 'nullable|string|max:255',
            'S_ID' => 'nullable|string|max:255',
            'P_ID' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
            'victime' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'numinter' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'modeinter' => 'nullable|string|max:255',
            'vehicule' => 'nullable|string|max:255',
            'materiel' => 'nullable|string|max:255',
            'person' => 'nullable|string|max:255',
            'nfid' => 'nullable|string|max:255',
            'dossier' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'security' => 'nullable|string|max:255',
            'from' => 'nullable|string|max:255',
            'userfile[]' => 'nullable|file',
        ]);

        $item->update([
            'operation' => $validated['operation'] ?? null,
            'S_ID' => $validated['S_ID'] ?? null,
            'P_ID' => $validated['P_ID'] ?? null,
            'status' => $validated['status'] ?? null,
            'section' => $validated['section'] ?? null,
            'victime' => $validated['victime'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'numinter' => $validated['numinter'] ?? null,
            'action' => $validated['action'] ?? null,
            'modeinter' => $validated['modeinter'] ?? null,
            'vehicule' => $validated['vehicule'] ?? null,
            'materiel' => $validated['materiel'] ?? null,
            'person' => $validated['person'] ?? null,
            'nfid' => $validated['nfid'] ?? null,
            'dossier' => $validated['dossier'] ?? null,
            'type' => $validated['type'] ?? null,
            'security' => $validated['security'] ?? null,
            'from' => $validated['from'] ?? null,
            'userfile[]' => $validated['userfile[]'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_document.edit', $item->id)
            ->with('success', 'Document updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Document::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.upd_document.index')
            ->with('success', 'Document deleted successfully');
    }
                
}
