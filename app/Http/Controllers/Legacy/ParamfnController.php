<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Paramfn;
use Illuminate\Http\Request;

/**
 * Legacy migration source: paramfn.php
 * Legacy pattern: list
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class ParamfnController extends Controller
{
    public function index(Request $request)
    {
        $query = Paramfn::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('te_code', 'like', '%' . $term . '%');
                $query->orWhere('tp_id', 'like', '%' . $term . '%');
                $query->orWhere('tp_libelle', 'like', '%' . $term . '%');
                $query->orWhere('tp_num', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.paramfn.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.paramfn.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Paramfn::findOrFail($id);

        return view('legacy_migrated.paramfn.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type_evenement' => 'nullable|string|max:255',
        ]);

        $item = Paramfn::create([
            'type_evenement' => $validated['type_evenement'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.paramfn.edit', $item->id)
            ->with('success', 'Paramfn created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Paramfn::findOrFail($id);

        $validated = $request->validate([
            'type_evenement' => 'nullable|string|max:255',
        ]);

        $item->update([
            'type_evenement' => $validated['type_evenement'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.paramfn.edit', $item->id)
            ->with('success', 'Paramfn updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Paramfn::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.paramfn.index')
            ->with('success', 'Paramfn deleted successfully');
    }
                
}
