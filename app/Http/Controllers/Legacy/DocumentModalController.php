<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;

/**
 * Legacy migration source: document_modal.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class DocumentModalController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('d_id', 'like', '%' . $term . '%');
                $query->orWhere('s_id', 'like', '%' . $term . '%');
                $query->orWhere('v_id', 'like', '%' . $term . '%');
                $query->orWhere('m_id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.document_modal.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.document_modal.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Document::findOrFail($id);

        return view('legacy_migrated.document_modal.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'operation' => 'nullable|string|max:255',
            'P_ID' => 'nullable|string|max:255',
            'doc' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'S_ID' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'docid' => 'nullable|string|max:255',
            'isfolder' => 'nullable|string|max:255',
            'foldername' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'security' => 'nullable|string|max:255',
            'parentfolder' => 'nullable|string|max:255',
        ]);

        $item = Document::create([
            'operation' => $validated['operation'] ?? null,
            'P_ID' => $validated['P_ID'] ?? null,
            'doc' => $validated['doc'] ?? null,
            'action' => $validated['action'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'S_ID' => $validated['S_ID'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'docid' => $validated['docid'] ?? null,
            'isfolder' => $validated['isfolder'] ?? null,
            'foldername' => $validated['foldername'] ?? null,
            'type' => $validated['type'] ?? null,
            'security' => $validated['security'] ?? null,
            'parentfolder' => $validated['parentfolder'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.document_modal.edit', $item->id)
            ->with('success', 'Document created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Document::findOrFail($id);

        $validated = $request->validate([
            'operation' => 'nullable|string|max:255',
            'P_ID' => 'nullable|string|max:255',
            'doc' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'S_ID' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'docid' => 'nullable|string|max:255',
            'isfolder' => 'nullable|string|max:255',
            'foldername' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'security' => 'nullable|string|max:255',
            'parentfolder' => 'nullable|string|max:255',
        ]);

        $item->update([
            'operation' => $validated['operation'] ?? null,
            'P_ID' => $validated['P_ID'] ?? null,
            'doc' => $validated['doc'] ?? null,
            'action' => $validated['action'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'S_ID' => $validated['S_ID'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'docid' => $validated['docid'] ?? null,
            'isfolder' => $validated['isfolder'] ?? null,
            'foldername' => $validated['foldername'] ?? null,
            'type' => $validated['type'] ?? null,
            'security' => $validated['security'] ?? null,
            'parentfolder' => $validated['parentfolder'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.document_modal.edit', $item->id)
            ->with('success', 'Document updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Document::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.document_modal.index')
            ->with('success', 'Document deleted successfully');
    }
                
}
