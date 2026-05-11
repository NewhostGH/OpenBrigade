<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\FonctionsSms;
use Illuminate\Http\Request;

/**
 * Legacy migration source: fonctions_sms.php
 * Legacy pattern: list
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class FonctionsSmsController extends Controller
{
    public function index(Request $request)
    {
        $query = FonctionsSms::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('s_id', 'like', '%' . $term . '%');
                $query->orWhere('sms_local_provider', 'like', '%' . $term . '%');
                $query->orWhere('sms_local_user', 'like', '%' . $term . '%');
                $query->orWhere('sms_local_password', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.fonctions_sms.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.fonctions_sms.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = FonctionsSms::findOrFail($id);

        return view('legacy_migrated.fonctions_sms.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = FonctionsSms::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.fonctions_sms.edit', $item->id)
            ->with('success', 'FonctionsSms created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = FonctionsSms::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.fonctions_sms.edit', $item->id)
            ->with('success', 'FonctionsSms updated successfully');
    }
                

    public function destroy($id)
    {
        $item = FonctionsSms::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.fonctions_sms.index')
            ->with('success', 'FonctionsSms deleted successfully');
    }
                
}
