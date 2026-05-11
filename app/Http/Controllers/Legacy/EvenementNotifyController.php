<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EvenementNotify;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_notify.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementNotifyController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementNotify::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('e_code', 'like', '%' . $term . '%');
                $query->orWhere('eh_id_eh_id', 'like', '%' . $term . '%');
                $query->orWhere('s_id', 'like', '%' . $term . '%');
                $query->orWhere('te_code', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.evenement_notify.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.evenement_notify.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = EvenementNotify::findOrFail($id);

        return view('legacy_migrated.evenement_notify.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = EvenementNotify::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.evenement_notify.edit', $item->id)
            ->with('success', 'EvenementNotify created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = EvenementNotify::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.evenement_notify.edit', $item->id)
            ->with('success', 'EvenementNotify updated successfully');
    }
                

    public function destroy($id)
    {
        $item = EvenementNotify::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.evenement_notify.index')
            ->with('success', 'EvenementNotify deleted successfully');
    }
                
}
