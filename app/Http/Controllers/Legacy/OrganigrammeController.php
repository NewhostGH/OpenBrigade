<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Organigramme;
use Illuminate\Http\Request;

/**
 * Legacy migration source: organigramme.php
 * Legacy pattern: list
 * Legacy permission id: 44
 * This file stems from a legacy migration and requires functional verification.
 */
class OrganigrammeController extends Controller
{
    public function index(Request $request)
    {
        $query = Organigramme::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('gp_id', 'like', '%' . $term . '%');
                $query->orWhere('gp_description', 'like', '%' . $term . '%');
                $query->orWhere('tr_sub_possible', 'like', '%' . $term . '%');
                $query->orWhere('p_id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.organigramme.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.organigramme.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Organigramme::findOrFail($id);

        return view('legacy_migrated.organigramme.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Organigramme::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.organigramme.edit', $item->id)
            ->with('success', 'Organigramme created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Organigramme::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.organigramme.edit', $item->id)
            ->with('success', 'Organigramme updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Organigramme::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.organigramme.index')
            ->with('success', 'Organigramme deleted successfully');
    }
                
}
