<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\ElementFacturable;
use Illuminate\Http\Request;

/**
 * Legacy migration source: upd_element_facturable.php
 * Legacy pattern: edit
 * Legacy permission id: 29
 * This file stems from a legacy migration and requires functional verification.
 */
class UpdElementFacturableController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.upd_element_facturable.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = ElementFacturable::findOrFail($id);

        return view('legacy_migrated.upd_element_facturable.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'EF_ID' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'EF_NAME' => 'nullable|string|max:255',
            'EF_PRICE' => 'nullable|string|max:255',
            'Dupliquer' => 'nullable|string|max:255',
            'annuler' => 'nullable|string|max:255',
            'S_ID' => 'nullable|string|max:255',
            'TEF_CODE' => 'nullable|string|max:255',
        ]);

        $item = ElementFacturable::create([
            'EF_ID' => $validated['EF_ID'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'EF_NAME' => $validated['EF_NAME'] ?? null,
            'EF_PRICE' => $validated['EF_PRICE'] ?? null,
            'Dupliquer' => $validated['Dupliquer'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
            'S_ID' => $validated['S_ID'] ?? null,
            'TEF_CODE' => $validated['TEF_CODE'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_element_facturable.edit', $item->id)
            ->with('success', 'ElementFacturable created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = ElementFacturable::findOrFail($id);

        $validated = $request->validate([
            'EF_ID' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'EF_NAME' => 'nullable|string|max:255',
            'EF_PRICE' => 'nullable|string|max:255',
            'Dupliquer' => 'nullable|string|max:255',
            'annuler' => 'nullable|string|max:255',
            'S_ID' => 'nullable|string|max:255',
            'TEF_CODE' => 'nullable|string|max:255',
        ]);

        $item->update([
            'EF_ID' => $validated['EF_ID'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'EF_NAME' => $validated['EF_NAME'] ?? null,
            'EF_PRICE' => $validated['EF_PRICE'] ?? null,
            'Dupliquer' => $validated['Dupliquer'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
            'S_ID' => $validated['S_ID'] ?? null,
            'TEF_CODE' => $validated['TEF_CODE'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_element_facturable.edit', $item->id)
            ->with('success', 'ElementFacturable updated successfully');
    }
                

    public function destroy($id)
    {
        $item = ElementFacturable::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.upd_element_facturable.index')
            ->with('success', 'ElementFacturable deleted successfully');
    }
                
}
