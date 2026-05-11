<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EvenementChoice;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_choice.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementChoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementChoice::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }
        if (session()->has('SES_COMPANY')) {
            $query->where('company_id', session('SES_COMPANY'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('activit', 'like', '%' . $term . '%');
                $query->orWhere('dps', 'like', '%' . $term . '%');
                $query->orWhere('lieu', 'like', '%' . $term . '%');
                $query->orWhere('date', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.evenement_choice.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.evenement_choice.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EvenementChoice::findOrFail($id);

        return view('legacy_migrated.evenement_choice.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'delCal' => 'nullable|string|max:255',
            'AddCal' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'ps' => 'nullable|string|max:255',
        ]);

        $item = EvenementChoice::create([
            'sub' => $validated['sub'] ?? null,
            'delCal' => $validated['delCal'] ?? null,
            'AddCal' => $validated['AddCal'] ?? null,
            'company' => $validated['company'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'type' => $validated['type'] ?? null,
            'ps' => $validated['ps'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_choice.edit', $item->id)
            ->with('success', 'EvenementChoice created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EvenementChoice::findOrFail($id);

        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'delCal' => 'nullable|string|max:255',
            'AddCal' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'ps' => 'nullable|string|max:255',
        ]);

        $item->update([
            'sub' => $validated['sub'] ?? null,
            'delCal' => $validated['delCal'] ?? null,
            'AddCal' => $validated['AddCal'] ?? null,
            'company' => $validated['company'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'type' => $validated['type'] ?? null,
            'ps' => $validated['ps'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.evenement_choice.edit', $item->id)
            ->with('success', 'EvenementChoice updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EvenementChoice::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenement_choice.index')
            ->with('success', 'EvenementChoice deleted successfully');
    }
                
}
