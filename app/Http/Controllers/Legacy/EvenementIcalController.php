<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EvenementIcal;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_ical.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementIcalController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementIcal::query();
        if (session()->has('SES_COMPANY')) {
            $query->where('company_id', session('SES_COMPANY'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('e_code', 'like', '%' . $term . '%');
                $query->orWhere('eh_id', 'like', '%' . $term . '%');
                $query->orWhere('eh_date_debut', 'like', '%' . $term . '%');
                $query->orWhere('eh_debut', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.evenement_ical.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.evenement_ical.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EvenementIcal::findOrFail($id);

        return view('legacy_migrated.evenement_ical.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = EvenementIcal::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.evenement_ical.edit', $item->id)
            ->with('success', 'EvenementIcal created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EvenementIcal::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.evenement_ical.edit', $item->id)
            ->with('success', 'EvenementIcal updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EvenementIcal::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenement_ical.index')
            ->with('success', 'EvenementIcal deleted successfully');
    }
                
}
