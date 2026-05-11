<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\ConsommableLoad;
use Illuminate\Http\Request;

/**
 * Legacy migration source: consommable_load.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class ConsommableLoadController extends Controller
{
    public function index(Request $request)
    {
        $query = ConsommableLoad::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }
        if (session()->has('SES_COMPANY')) {
            $query->where('company_id', session('SES_COMPANY'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.consommable_load.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.consommable_load.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = ConsommableLoad::findOrFail($id);

        return view('legacy_migrated.consommable_load.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = ConsommableLoad::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.consommable_load.edit', $item->id)
            ->with('success', 'ConsommableLoad created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = ConsommableLoad::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.consommable_load.edit', $item->id)
            ->with('success', 'ConsommableLoad updated successfully');
    }
                

    public function destroy($id)
    {
        $item = ConsommableLoad::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.consommable_load.index')
            ->with('success', 'ConsommableLoad deleted successfully');
    }
                
}
