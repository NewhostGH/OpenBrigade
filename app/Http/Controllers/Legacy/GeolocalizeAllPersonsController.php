<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\GeolocalizeAllPersons;
use Illuminate\Http\Request;

/**
 * Legacy migration source: geolocalize_all_persons.php
 * Legacy pattern: list
 * Legacy permission id: 14
 * This file stems from a legacy migration and requires functional verification.
 */
class GeolocalizeAllPersonsController extends Controller
{
    public function index(Request $request)
    {
        $query = GeolocalizeAllPersons::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.geolocalize_all_persons.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.geolocalize_all_persons.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = GeolocalizeAllPersons::findOrFail($id);

        return view('legacy_migrated.geolocalize_all_persons.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = GeolocalizeAllPersons::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.geolocalize_all_persons.edit', $item->id)
            ->with('success', 'GeolocalizeAllPersons created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = GeolocalizeAllPersons::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.geolocalize_all_persons.edit', $item->id)
            ->with('success', 'GeolocalizeAllPersons updated successfully');
    }
                

    public function destroy($id)
    {
        $item = GeolocalizeAllPersons::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.geolocalize_all_persons.index')
            ->with('success', 'GeolocalizeAllPersons deleted successfully');
    }
                
}
