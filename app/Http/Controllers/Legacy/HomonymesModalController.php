<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Homonymes;
use Illuminate\Http\Request;

/**
 * Legacy migration source: homonymes_modal.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class HomonymesModalController extends Controller
{
    public function index(Request $request)
    {
        $query = Homonymes::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
                $query->orWhere('date_formatp_birthdate', 'like', '%' . $term . '%');
                $query->orWhere('dmyp_birthdate0', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.homonymes_modal.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.homonymes_modal.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Homonymes::findOrFail($id);

        return view('legacy_migrated.homonymes_modal.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Homonymes::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.homonymes_modal.edit', $item->id)
            ->with('success', 'Homonymes created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Homonymes::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.homonymes_modal.edit', $item->id)
            ->with('success', 'Homonymes updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Homonymes::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.homonymes_modal.index')
            ->with('success', 'Homonymes deleted successfully');
    }
                
}
