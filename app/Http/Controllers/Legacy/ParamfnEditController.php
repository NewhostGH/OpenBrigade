<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\ParamfnEdit;
use Illuminate\Http\Request;

/**
 * Legacy migration source: paramfn_edit.php
 * Legacy pattern: list
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class ParamfnEditController extends Controller
{
    public function index(Request $request)
    {
        $query = ParamfnEdit::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('te_code', 'like', '%' . $term . '%');
                $query->orWhere('tp_num', 'like', '%' . $term . '%');
                $query->orWhere('tp_libelle', 'like', '%' . $term . '%');
                $query->orWhere('instructor', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.paramfn_edit.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.paramfn_edit.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = ParamfnEdit::findOrFail($id);

        return view('legacy_migrated.paramfn_edit.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'operation' => 'nullable|string|max:255',
            'TP_ID' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'TP_LIBELLE' => 'nullable|string|max:255',
            'INSTRUCTOR' => 'nullable|string|max:255',
            'TE_CODE' => 'nullable|string|max:255',
            'EQ_ID' => 'nullable|string|max:255',
            'TP_NUM' => 'nullable|string|max:255',
            'PS_ID' => 'nullable|string|max:255',
            'PS_ID2' => 'nullable|string|max:255',
        ]);

        $item = ParamfnEdit::create([
            'operation' => $validated['operation'] ?? null,
            'TP_ID' => $validated['TP_ID'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'TP_LIBELLE' => $validated['TP_LIBELLE'] ?? null,
            'INSTRUCTOR' => $validated['INSTRUCTOR'] ?? null,
            'TE_CODE' => $validated['TE_CODE'] ?? null,
            'EQ_ID' => $validated['EQ_ID'] ?? null,
            'TP_NUM' => $validated['TP_NUM'] ?? null,
            'PS_ID' => $validated['PS_ID'] ?? null,
            'PS_ID2' => $validated['PS_ID2'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.paramfn_edit.edit', $item->id)
            ->with('success', 'ParamfnEdit created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = ParamfnEdit::findOrFail($id);

        $validated = $request->validate([
            'operation' => 'nullable|string|max:255',
            'TP_ID' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'TP_LIBELLE' => 'nullable|string|max:255',
            'INSTRUCTOR' => 'nullable|string|max:255',
            'TE_CODE' => 'nullable|string|max:255',
            'EQ_ID' => 'nullable|string|max:255',
            'TP_NUM' => 'nullable|string|max:255',
            'PS_ID' => 'nullable|string|max:255',
            'PS_ID2' => 'nullable|string|max:255',
        ]);

        $item->update([
            'operation' => $validated['operation'] ?? null,
            'TP_ID' => $validated['TP_ID'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'TP_LIBELLE' => $validated['TP_LIBELLE'] ?? null,
            'INSTRUCTOR' => $validated['INSTRUCTOR'] ?? null,
            'TE_CODE' => $validated['TE_CODE'] ?? null,
            'EQ_ID' => $validated['EQ_ID'] ?? null,
            'TP_NUM' => $validated['TP_NUM'] ?? null,
            'PS_ID' => $validated['PS_ID'] ?? null,
            'PS_ID2' => $validated['PS_ID2'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.paramfn_edit.edit', $item->id)
            ->with('success', 'ParamfnEdit updated successfully');
    }
                

    public function destroy($id)
    {
        $item = ParamfnEdit::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.paramfn_edit.index')
            ->with('success', 'ParamfnEdit deleted successfully');
    }
                
}
