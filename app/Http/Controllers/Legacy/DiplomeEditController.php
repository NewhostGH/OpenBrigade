<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\DiplomeEdit;
use Illuminate\Http\Request;

/**
 * Legacy migration source: diplome_edit.php
 * Legacy pattern: list
 * Legacy permission id: 54
 * This file stems from a legacy migration and requires functional verification.
 */
class DiplomeEditController extends Controller
{
    public function index(Request $request)
    {
        $query = DiplomeEdit::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('submitscriptheadphphelplediplmedoittreimprimsurunepageauformata4210mmx297mm', 'like', '%' . $term . '%');
                $query->orWhere('latailledecaractre', 'like', '%' . $term . '%');
                $query->orWhere('lechampxcorrespondlabscisse', 'like', '%' . $term . '%');
                $query->orWhere('lechampycorrespondlordonne', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.diplome_edit.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.diplome_edit.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = DiplomeEdit::findOrFail($id);

        return view('legacy_migrated.diplome_edit.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'annexe[' => 'nullable|string|max:255',
            'userfile' => 'nullable|file',
            'actif[' => 'nullable|string|max:255',
            'affichage[' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'psid' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'selectdiplome' => 'nullable|string|max:255',
            'aff_taille[' => 'nullable|string|max:255',
            'aff_style[' => 'nullable|string|max:255',
            'aff_police[' => 'nullable|string|max:255',
        ]);

        $item = DiplomeEdit::create([
            'annexe[' => $validated['annexe['] ?? null,
            'userfile' => $validated['userfile'] ?? null,
            'actif[' => $validated['actif['] ?? null,
            'affichage[' => $validated['affichage['] ?? null,
            'action' => $validated['action'] ?? null,
            'psid' => $validated['psid'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'selectdiplome' => $validated['selectdiplome'] ?? null,
            'aff_taille[' => $validated['aff_taille['] ?? null,
            'aff_style[' => $validated['aff_style['] ?? null,
            'aff_police[' => $validated['aff_police['] ?? null,
        ]);

        return redirect()->route('legacy_migrated.diplome_edit.edit', $item->id)
            ->with('success', 'DiplomeEdit created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = DiplomeEdit::findOrFail($id);

        $validated = $request->validate([
            'annexe[' => 'nullable|string|max:255',
            'userfile' => 'nullable|file',
            'actif[' => 'nullable|string|max:255',
            'affichage[' => 'nullable|string|max:255',
            'action' => 'nullable|string|max:255',
            'psid' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'selectdiplome' => 'nullable|string|max:255',
            'aff_taille[' => 'nullable|string|max:255',
            'aff_style[' => 'nullable|string|max:255',
            'aff_police[' => 'nullable|string|max:255',
        ]);

        $item->update([
            'annexe[' => $validated['annexe['] ?? null,
            'userfile' => $validated['userfile'] ?? null,
            'actif[' => $validated['actif['] ?? null,
            'affichage[' => $validated['affichage['] ?? null,
            'action' => $validated['action'] ?? null,
            'psid' => $validated['psid'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'selectdiplome' => $validated['selectdiplome'] ?? null,
            'aff_taille[' => $validated['aff_taille['] ?? null,
            'aff_style[' => $validated['aff_style['] ?? null,
            'aff_police[' => $validated['aff_police['] ?? null,
        ]);

        return redirect()->route('legacy_migrated.diplome_edit.edit', $item->id)
            ->with('success', 'DiplomeEdit updated successfully');
    }
                

    public function destroy($id)
    {
        $item = DiplomeEdit::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.diplome_edit.index')
            ->with('success', 'DiplomeEdit deleted successfully');
    }
                
}
