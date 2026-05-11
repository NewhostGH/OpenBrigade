<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\HomonymesManage;
use Illuminate\Http\Request;

/**
 * Legacy migration source: homonymes_manage.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class HomonymesManageController extends Controller
{
    public function index(Request $request)
    {
        $query = HomonymesManage::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_id', 'like', '%' . $term . '%');
                $query->orWhere('p_nomp_nom0', 'like', '%' . $term . '%');
                $query->orWhere('p_nom_naissance', 'like', '%' . $term . '%');
                $query->orWhere('p_prenomp_prenom0', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.homonymes_manage.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.homonymes_manage.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = HomonymesManage::findOrFail($id);

        return view('legacy_migrated.homonymes_manage.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pid' => 'nullable|string|max:255',
            'doublon_id' => 'nullable|string|max:255',
            'competences' => 'nullable|string|max:255',
            'formations' => 'nullable|string|max:255',
            'participations' => 'nullable|string|max:255',
            'radier' => 'nullable|string|max:255',
            'supprimer' => 'nullable|string|max:255',
        ]);

        $item = HomonymesManage::create([
            'pid' => $validated['pid'] ?? null,
            'doublon_id' => $validated['doublon_id'] ?? null,
            'competences' => $validated['competences'] ?? null,
            'formations' => $validated['formations'] ?? null,
            'participations' => $validated['participations'] ?? null,
            'radier' => $validated['radier'] ?? null,
            'supprimer' => $validated['supprimer'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.homonymes_manage.edit', $item->id)
            ->with('success', 'HomonymesManage created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = HomonymesManage::findOrFail($id);

        $validated = $request->validate([
            'pid' => 'nullable|string|max:255',
            'doublon_id' => 'nullable|string|max:255',
            'competences' => 'nullable|string|max:255',
            'formations' => 'nullable|string|max:255',
            'participations' => 'nullable|string|max:255',
            'radier' => 'nullable|string|max:255',
            'supprimer' => 'nullable|string|max:255',
        ]);

        $item->update([
            'pid' => $validated['pid'] ?? null,
            'doublon_id' => $validated['doublon_id'] ?? null,
            'competences' => $validated['competences'] ?? null,
            'formations' => $validated['formations'] ?? null,
            'participations' => $validated['participations'] ?? null,
            'radier' => $validated['radier'] ?? null,
            'supprimer' => $validated['supprimer'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.homonymes_manage.edit', $item->id)
            ->with('success', 'HomonymesManage updated successfully');
    }
                

    public function destroy($id)
    {
        $item = HomonymesManage::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.homonymes_manage.index')
            ->with('success', 'HomonymesManage deleted successfully');
    }
                
}
