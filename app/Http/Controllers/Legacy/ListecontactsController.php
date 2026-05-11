<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Listecontacts;
use Illuminate\Http\Request;

/**
 * Legacy migration source: listecontacts.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class ListecontactsController extends Controller
{
    public function index(Request $request)
    {
        $query = Listecontacts::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
                $query->orWhere('p_email', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.listecontacts.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.listecontacts.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Listecontacts::findOrFail($id);

        return view('legacy_migrated.listecontacts.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'SelectionMail' => 'nullable|string|max:255',
        ]);

        $item = Listecontacts::create([
            'SelectionMail' => $validated['SelectionMail'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.listecontacts.edit', $item->id)
            ->with('success', 'Listecontacts created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Listecontacts::findOrFail($id);

        $validated = $request->validate([
            'SelectionMail' => 'nullable|string|max:255',
        ]);

        $item->update([
            'SelectionMail' => $validated['SelectionMail'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.listecontacts.edit', $item->id)
            ->with('success', 'Listecontacts updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Listecontacts::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.listecontacts.index')
            ->with('success', 'Listecontacts deleted successfully');
    }
                
}
