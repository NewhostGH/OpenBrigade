<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\LostPassword;
use Illuminate\Http\Request;

/**
 * Legacy migration source: lost_password.php
 * Legacy pattern: generic
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class LostPasswordController extends Controller
{
    public function index(Request $request)
    {
        $query = LostPassword::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_id', 'like', '%' . $term . '%');
                $query->orWhere('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
                $query->orWhere('p_email', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.lost_password.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.lost_password.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = LostPassword::findOrFail($id);

        return view('legacy_migrated.lost_password.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'matricule' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'recovery' => 'nullable|string|max:255',
        ]);

        $item = LostPassword::create([
            'matricule' => $validated['matricule'] ?? null,
            'email' => $validated['email'] ?? null,
            'recovery' => $validated['recovery'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.lost_password.edit', $item->id)
            ->with('success', 'LostPassword created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = LostPassword::findOrFail($id);

        $validated = $request->validate([
            'matricule' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'recovery' => 'nullable|string|max:255',
        ]);

        $item->update([
            'matricule' => $validated['matricule'] ?? null,
            'email' => $validated['email'] ?? null,
            'recovery' => $validated['recovery'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.lost_password.edit', $item->id)
            ->with('success', 'LostPassword updated successfully');
    }
                

    public function destroy($id)
    {
        $item = LostPassword::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.lost_password.index')
            ->with('success', 'LostPassword deleted successfully');
    }
                
}
