<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\IndispoStatus;
use Illuminate\Http\Request;

/**
 * Legacy migration source: indispo_status.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class IndispoStatusController extends Controller
{
    public function index(Request $request)
    {
        $query = IndispoStatus::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_id', 'like', '%' . $term . '%');
                $query->orWhere('i_debut', 'like', '%' . $term . '%');
                $query->orWhere('dmy', 'like', '%' . $term . '%');
                $query->orWhere('i_fin', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.indispo_status.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.indispo_status.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = IndispoStatus::findOrFail($id);

        return view('legacy_migrated.indispo_status.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = IndispoStatus::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.indispo_status.edit', $item->id)
            ->with('success', 'IndispoStatus created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = IndispoStatus::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.indispo_status.edit', $item->id)
            ->with('success', 'IndispoStatus updated successfully');
    }
                

    public function destroy($id)
    {
        $item = IndispoStatus::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.indispo_status.index')
            ->with('success', 'IndispoStatus deleted successfully');
    }
                
}
