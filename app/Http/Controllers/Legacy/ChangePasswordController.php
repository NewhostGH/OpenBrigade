<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\ChangePassword;
use Illuminate\Http\Request;

/**
 * Legacy migration source: change_password.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class ChangePasswordController extends Controller
{
    public function index(Request $request)
    {
        $query = ChangePassword::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
                $query->orWhere('p_mdp_expiry', 'like', '%' . $term . '%');
                $query->orWhere('datediffp_mdp_expiry', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.change_password.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.change_password.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = ChangePassword::findOrFail($id);

        return view('legacy_migrated.change_password.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'current' => 'nullable|string|max:255',
            'new1' => 'nullable|string|max:255',
            'new2' => 'nullable|string|max:255',
        ]);

        $item = ChangePassword::create([
            'current' => $validated['current'] ?? null,
            'new1' => $validated['new1'] ?? null,
            'new2' => $validated['new2'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.change_password.edit', $item->id)
            ->with('success', 'ChangePassword created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = ChangePassword::findOrFail($id);

        $validated = $request->validate([
            'current' => 'nullable|string|max:255',
            'new1' => 'nullable|string|max:255',
            'new2' => 'nullable|string|max:255',
        ]);

        $item->update([
            'current' => $validated['current'] ?? null,
            'new1' => $validated['new1'] ?? null,
            'new2' => $validated['new2'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.change_password.edit', $item->id)
            ->with('success', 'ChangePassword updated successfully');
    }
                

    public function destroy($id)
    {
        $item = ChangePassword::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.change_password.index')
            ->with('success', 'ChangePassword deleted successfully');
    }
                
}
