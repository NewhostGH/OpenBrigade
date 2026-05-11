<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\LostSession;
use Illuminate\Http\Request;

/**
 * Legacy migration source: lost_session.php
 * Legacy pattern: generic
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class LostSessionController extends Controller
{
    public function index(Request $request)
    {
        $query = LostSession::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.lost_session.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.lost_session.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = LostSession::findOrFail($id);

        return view('legacy_migrated.lost_session.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = LostSession::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.lost_session.edit', $item->id)
            ->with('success', 'LostSession created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = LostSession::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.lost_session.edit', $item->id)
            ->with('success', 'LostSession updated successfully');
    }
                

    public function destroy($id)
    {
        $item = LostSession::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.lost_session.index')
            ->with('success', 'LostSession deleted successfully');
    }
                
}
