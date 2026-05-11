<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\CavEdit;
use Illuminate\Http\Request;

/**
 * Legacy migration source: cav_edit.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class CavEditController extends Controller
{
    public function index(Request $request)
    {
        $query = CavEdit::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('cav_responsable', 'like', '%' . $term . '%');
                $query->orWhere('e_code', 'like', '%' . $term . '%');
                $query->orWhere('vi_id', 'like', '%' . $term . '%');
                $query->orWhere('maxcav_id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.cav_edit.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.cav_edit.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = CavEdit::findOrFail($id);

        return view('legacy_migrated.cav_edit.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'comptage' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'commentaire' => 'nullable|string|max:255',
            'responsable' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'numcav' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'ouvert' => 'nullable|string|max:255',
        ]);

        $item = CavEdit::create([
            'comptage' => $validated['comptage'] ?? null,
            'address' => $validated['address'] ?? null,
            'commentaire' => $validated['commentaire'] ?? null,
            'responsable' => $validated['responsable'] ?? null,
            'action' => $validated['action'] ?? null,
            'numcav' => $validated['numcav'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'name' => $validated['name'] ?? null,
            'ouvert' => $validated['ouvert'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.cav_edit.edit', $item->id)
            ->with('success', 'CavEdit created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = CavEdit::findOrFail($id);

        $validated = $request->validate([
            'comptage' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'commentaire' => 'nullable|string|max:255',
            'responsable' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'numcav' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'ouvert' => 'nullable|string|max:255',
        ]);

        $item->update([
            'comptage' => $validated['comptage'] ?? null,
            'address' => $validated['address'] ?? null,
            'commentaire' => $validated['commentaire'] ?? null,
            'responsable' => $validated['responsable'] ?? null,
            'action' => $validated['action'] ?? null,
            'numcav' => $validated['numcav'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'name' => $validated['name'] ?? null,
            'ouvert' => $validated['ouvert'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.cav_edit.edit', $item->id)
            ->with('success', 'CavEdit updated successfully');
    }
                

    public function destroy($id)
    {
        $item = CavEdit::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.cav_edit.index')
            ->with('success', 'CavEdit deleted successfully');
    }
                
}
