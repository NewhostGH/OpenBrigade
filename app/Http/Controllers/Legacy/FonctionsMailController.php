<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\FonctionsMail;
use Illuminate\Http\Request;

/**
 * Legacy migration source: fonctions_mail.php
 * Legacy pattern: list
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class FonctionsMailController extends Controller
{
    public function index(Request $request)
    {
        $query = FonctionsMail::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('distinctp_email', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.fonctions_mail.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.fonctions_mail.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = FonctionsMail::findOrFail($id);

        return view('legacy_migrated.fonctions_mail.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = FonctionsMail::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.fonctions_mail.edit', $item->id)
            ->with('success', 'FonctionsMail created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = FonctionsMail::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.fonctions_mail.edit', $item->id)
            ->with('success', 'FonctionsMail updated successfully');
    }
                

    public function destroy($id)
    {
        $item = FonctionsMail::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.fonctions_mail.index')
            ->with('success', 'FonctionsMail deleted successfully');
    }
                
}
