<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Localize;
use Illuminate\Http\Request;

/**
 * Legacy migration source: localize.php
 * Legacy pattern: list
 * Legacy permission id: 43
 * This file stems from a legacy migration and requires functional verification.
 */
class LocalizeController extends Controller
{
    public function index(Request $request)
    {
        $query = Localize::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_id', 'like', '%' . $term . '%');
                $query->orWhere('upperp_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.localize.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.localize.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Localize::findOrFail($id);

        return view('legacy_migrated.localize.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'nullable|string|max:255',
            'annuler' => 'nullable|string|max:255',
            'liste2' => 'nullable|string|max:255',
            'SelectionMail' => 'nullable|string|max:255',
        ]);

        $item = Localize::create([
            'phone' => $validated['phone'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
            'liste2' => $validated['liste2'] ?? null,
            'SelectionMail' => $validated['SelectionMail'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.localize.edit', $item->id)
            ->with('success', 'Localize created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Localize::findOrFail($id);

        $validated = $request->validate([
            'phone' => 'nullable|string|max:255',
            'annuler' => 'nullable|string|max:255',
            'liste2' => 'nullable|string|max:255',
            'SelectionMail' => 'nullable|string|max:255',
        ]);

        $item->update([
            'phone' => $validated['phone'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
            'liste2' => $validated['liste2'] ?? null,
            'SelectionMail' => $validated['SelectionMail'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.localize.edit', $item->id)
            ->with('success', 'Localize updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Localize::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.localize.index')
            ->with('success', 'Localize deleted successfully');
    }
                
}
