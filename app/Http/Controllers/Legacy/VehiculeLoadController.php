<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\VehiculeLoad;
use Illuminate\Http\Request;

/**
 * Legacy migration source: vehicule_load.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class VehiculeLoadController extends Controller
{
    public function index(Request $request)
    {
        $query = VehiculeLoad::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }
        if (session()->has('SES_COMPANY')) {
            $query->where('company_id', session('SES_COMPANY'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
                $query->orWhere('p_old_member', 'like', '%' . $term . '%');
                $query->orWhere('count', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.vehicule_load.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.vehicule_load.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = VehiculeLoad::findOrFail($id);

        return view('legacy_migrated.vehicule_load.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = VehiculeLoad::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.vehicule_load.edit', $item->id)
            ->with('success', 'VehiculeLoad created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = VehiculeLoad::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.vehicule_load.edit', $item->id)
            ->with('success', 'VehiculeLoad updated successfully');
    }
                

    public function destroy($id)
    {
        $item = VehiculeLoad::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.vehicule_load.index')
            ->with('success', 'VehiculeLoad deleted successfully');
    }
                
}
