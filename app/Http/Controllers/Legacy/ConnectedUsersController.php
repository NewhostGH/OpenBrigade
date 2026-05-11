<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\ConnectedUsers;
use Illuminate\Http\Request;

/**
 * Legacy migration source: connected_users.php
 * Legacy pattern: generic
 * Legacy permission id: 20
 * This file stems from a legacy migration and requires functional verification.
 */
class ConnectedUsersController extends Controller
{
    public function index(Request $request)
    {
        $query = ConnectedUsers::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_photo', 'like', '%' . $term . '%');
                $query->orWhere('p_id', 'like', '%' . $term . '%');
                $query->orWhere('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.connected_users.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.connected_users.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = ConnectedUsers::findOrFail($id);

        return view('legacy_migrated.connected_users.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
        ]);

        $item = ConnectedUsers::create([
            'sub' => $validated['sub'] ?? null,
            'filter' => $validated['filter'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.connected_users.edit', $item->id)
            ->with('success', 'ConnectedUsers created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = ConnectedUsers::findOrFail($id);

        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
        ]);

        $item->update([
            'sub' => $validated['sub'] ?? null,
            'filter' => $validated['filter'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.connected_users.edit', $item->id)
            ->with('success', 'ConnectedUsers updated successfully');
    }
                

    public function destroy($id)
    {
        $item = ConnectedUsers::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.connected_users.index')
            ->with('success', 'ConnectedUsers deleted successfully');
    }
                
}
