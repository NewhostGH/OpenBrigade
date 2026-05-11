<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Membres;
use Illuminate\Http\Request;

/**
 * Legacy migration source: membres.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class MembresController extends Controller
{
    public function index(Request $request)
    {
        $query = Membres::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('gp_id', 'like', '%' . $term . '%');
                $query->orWhere('gp_description', 'like', '%' . $term . '%');
                $query->orWhere('p_id', 'like', '%' . $term . '%');
                $query->orWhere('p_nom', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.membres.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.membres.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Membres::findOrFail($id);

        return view('legacy_migrated.membres.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Membres::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.membres.edit', $item->id)
            ->with('success', 'Membres created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Membres::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.membres.edit', $item->id)
            ->with('success', 'Membres updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Membres::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.membres.index')
            ->with('success', 'Membres deleted successfully');
    }
                
}
