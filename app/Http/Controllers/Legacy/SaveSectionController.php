<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_section.php
 * Legacy pattern: save
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class SaveSectionController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_section.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Section::findOrFail($id);

        return view('legacy_migrated.save_section.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'S_ID' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'nom' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:255',
            'parent' => 'nullable|string|max:255',
            'ordre' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'address_complement' => 'nullable|string|max:255',
            'zipcode' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'phone2' => 'nullable|string|max:255',
            'phone3' => 'nullable|string|max:255',
            'fax' => 'nullable|string|max:255',
            'hide' => 'nullable|string|max:255',
            'SHOW_PHONE3' => 'nullable|string|max:255',
            'SHOW_EMAIL3' => 'nullable|string|max:255',
            'SHOW_URL' => 'nullable|string|max:255',
            'inactive' => 'nullable|string|max:255',
            'siret' => 'nullable|string|max:255',
        ]);

        $item = Section::create([
            'S_ID' => $validated['S_ID'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'nom' => $validated['nom'] ?? null,
            'code' => $validated['code'] ?? null,
            'parent' => $validated['parent'] ?? null,
            'ordre' => $validated['ordre'] ?? null,
            'address' => $validated['address'] ?? null,
            'address_complement' => $validated['address_complement'] ?? null,
            'zipcode' => $validated['zipcode'] ?? null,
            'city' => $validated['city'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'phone2' => $validated['phone2'] ?? null,
            'phone3' => $validated['phone3'] ?? null,
            'fax' => $validated['fax'] ?? null,
            'hide' => $validated['hide'] ?? null,
            'SHOW_PHONE3' => $validated['SHOW_PHONE3'] ?? null,
            'SHOW_EMAIL3' => $validated['SHOW_EMAIL3'] ?? null,
            'SHOW_URL' => $validated['SHOW_URL'] ?? null,
            'inactive' => $validated['inactive'] ?? null,
            'siret' => $validated['siret'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_section.edit', $item->id)
            ->with('success', 'Section created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Section::findOrFail($id);

        $validated = $request->validate([
            'S_ID' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'nom' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:255',
            'parent' => 'nullable|string|max:255',
            'ordre' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'address_complement' => 'nullable|string|max:255',
            'zipcode' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'phone2' => 'nullable|string|max:255',
            'phone3' => 'nullable|string|max:255',
            'fax' => 'nullable|string|max:255',
            'hide' => 'nullable|string|max:255',
            'SHOW_PHONE3' => 'nullable|string|max:255',
            'SHOW_EMAIL3' => 'nullable|string|max:255',
            'SHOW_URL' => 'nullable|string|max:255',
            'inactive' => 'nullable|string|max:255',
            'siret' => 'nullable|string|max:255',
        ]);

        $item->update([
            'S_ID' => $validated['S_ID'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'nom' => $validated['nom'] ?? null,
            'code' => $validated['code'] ?? null,
            'parent' => $validated['parent'] ?? null,
            'ordre' => $validated['ordre'] ?? null,
            'address' => $validated['address'] ?? null,
            'address_complement' => $validated['address_complement'] ?? null,
            'zipcode' => $validated['zipcode'] ?? null,
            'city' => $validated['city'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'phone2' => $validated['phone2'] ?? null,
            'phone3' => $validated['phone3'] ?? null,
            'fax' => $validated['fax'] ?? null,
            'hide' => $validated['hide'] ?? null,
            'SHOW_PHONE3' => $validated['SHOW_PHONE3'] ?? null,
            'SHOW_EMAIL3' => $validated['SHOW_EMAIL3'] ?? null,
            'SHOW_URL' => $validated['SHOW_URL'] ?? null,
            'inactive' => $validated['inactive'] ?? null,
            'siret' => $validated['siret'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.save_section.edit', $item->id)
            ->with('success', 'Section updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Section::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_section.index')
            ->with('success', 'Section deleted successfully');
    }
                

    public function applyOperation(Request $request)
    {
        $operation = (string) $request->input('operation');

        if ($operation === 'delete') {
            return response()->json(['status' => 'ok', 'operation' => 'delete']);
        }

        if ($operation === 'insert') {
            return response()->json(['status' => 'ok', 'operation' => 'insert']);
        }

        if ($operation === 'update') {
            return response()->json(['status' => 'ok', 'operation' => 'update']);
        }

        return response()->json(['status' => 'ignored', 'operation' => $operation]);
    }
}
