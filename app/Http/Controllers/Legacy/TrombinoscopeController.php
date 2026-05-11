<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Trombinoscope;
use Illuminate\Http\Request;

/**
 * Legacy migration source: trombinoscope.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class TrombinoscopeController extends Controller
{
    public function index(Request $request)
    {
        $query = Trombinoscope::query();
        if (session()->has('SES_COMPANY')) {
            $query->where('company_id', session('SES_COMPANY'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('count1', 'like', '%' . $term . '%');
                $query->orWhere('p_code', 'like', '%' . $term . '%');
                $query->orWhere('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.trombinoscope.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.trombinoscope.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Trombinoscope::findOrFail($id);

        return view('legacy_migrated.trombinoscope.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
        ]);

        $item = Trombinoscope::create([
            'sub' => $validated['sub'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'company' => $validated['company'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.trombinoscope.edit', $item->id)
            ->with('success', 'Trombinoscope created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Trombinoscope::findOrFail($id);

        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
        ]);

        $item->update([
            'sub' => $validated['sub'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'company' => $validated['company'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.trombinoscope.edit', $item->id)
            ->with('success', 'Trombinoscope updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Trombinoscope::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.trombinoscope.index')
            ->with('success', 'Trombinoscope deleted successfully');
    }
                
}
