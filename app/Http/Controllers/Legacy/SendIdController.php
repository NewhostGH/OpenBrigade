<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\SendId;
use Illuminate\Http\Request;

/**
 * Legacy migration source: send_id.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class SendIdController extends Controller
{
    public function index(Request $request)
    {
        $query = SendId::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_id', 'like', '%' . $term . '%');
                $query->orWhere('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_code', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.send_id.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.send_id.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = SendId::findOrFail($id);

        return view('legacy_migrated.send_id.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = SendId::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.send_id.edit', $item->id)
            ->with('success', 'SendId created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = SendId::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.send_id.edit', $item->id)
            ->with('success', 'SendId updated successfully');
    }
                

    public function destroy($id)
    {
        $item = SendId::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.send_id.index')
            ->with('success', 'SendId deleted successfully');
    }
                
}
