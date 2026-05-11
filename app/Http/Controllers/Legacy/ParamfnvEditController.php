<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\ParamfnvEdit;
use Illuminate\Http\Request;

/**
 * Legacy migration source: paramfnv_edit.php
 * Legacy pattern: generic
 * Legacy permission id: 18
 * This file stems from a legacy migration and requires functional verification.
 */
class ParamfnvEditController extends Controller
{
    public function index(Request $request)
    {
        $query = ParamfnvEdit::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('tfv_id', 'like', '%' . $term . '%');
                $query->orWhere('tfv_order', 'like', '%' . $term . '%');
                $query->orWhere('tfv_name', 'like', '%' . $term . '%');
                $query->orWhere('tfv_description', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.paramfnv_edit.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.paramfnv_edit.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = ParamfnvEdit::findOrFail($id);

        return view('legacy_migrated.paramfnv_edit.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'TFV_ID' => 'nullable|string|max:255',
            'TFV_NAME' => 'nullable|string|max:255',
            'TFV_DESCRIPTION' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'TFV_ORDER' => 'nullable|string|max:255',
        ]);

        $item = ParamfnvEdit::create([
            'TFV_ID' => $validated['TFV_ID'] ?? null,
            'TFV_NAME' => $validated['TFV_NAME'] ?? null,
            'TFV_DESCRIPTION' => $validated['TFV_DESCRIPTION'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'TFV_ORDER' => $validated['TFV_ORDER'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.paramfnv_edit.edit', $item->id)
            ->with('success', 'ParamfnvEdit created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = ParamfnvEdit::findOrFail($id);

        $validated = $request->validate([
            'TFV_ID' => 'nullable|string|max:255',
            'TFV_NAME' => 'nullable|string|max:255',
            'TFV_DESCRIPTION' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'TFV_ORDER' => 'nullable|string|max:255',
        ]);

        $item->update([
            'TFV_ID' => $validated['TFV_ID'] ?? null,
            'TFV_NAME' => $validated['TFV_NAME'] ?? null,
            'TFV_DESCRIPTION' => $validated['TFV_DESCRIPTION'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'TFV_ORDER' => $validated['TFV_ORDER'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.paramfnv_edit.edit', $item->id)
            ->with('success', 'ParamfnvEdit updated successfully');
    }
                

    public function destroy($id)
    {
        $item = ParamfnvEdit::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.paramfnv_edit.index')
            ->with('success', 'ParamfnvEdit deleted successfully');
    }
                
}
