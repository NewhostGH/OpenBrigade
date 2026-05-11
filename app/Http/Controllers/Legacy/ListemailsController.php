<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Listemails;
use Illuminate\Http\Request;

/**
 * Legacy migration source: listemails.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class ListemailsController extends Controller
{
    public function index(Request $request)
    {
        $query = Listemails::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
                $query->orWhere('p_email', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.listemails.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.listemails.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Listemails::findOrFail($id);

        return view('legacy_migrated.listemails.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'SelectionMail' => 'nullable|string|max:255',
        ]);

        $item = Listemails::create([
            'SelectionMail' => $validated['SelectionMail'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.listemails.edit', $item->id)
            ->with('success', 'Listemails created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Listemails::findOrFail($id);

        $validated = $request->validate([
            'SelectionMail' => 'nullable|string|max:255',
        ]);

        $item->update([
            'SelectionMail' => $validated['SelectionMail'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.listemails.edit', $item->id)
            ->with('success', 'Listemails updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Listemails::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.listemails.index')
            ->with('success', 'Listemails deleted successfully');
    }
                
}
