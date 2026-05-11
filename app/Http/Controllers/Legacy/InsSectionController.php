<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Http\Request;

/**
 * Legacy migration source: ins_section.php
 * Legacy pattern: create
 * Legacy permission id: 55
 * This file stems from a legacy migration and requires functional verification.
 */
class InsSectionController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.ins_section.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Section::findOrFail($id);

        return view('legacy_migrated.ins_section.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'S_ID' => 'nullable|string|max:255',
            'chef' => 'nullable|string|max:255',
            'adjoint' => 'nullable|string|max:255',
            'cadre' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:255',
            'nom' => 'nullable|string|max:255',
            'address_complement' => 'nullable|string|max:255',
            'zipcode' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'phone2' => 'nullable|string|max:255',
            'phone3' => 'nullable|string|max:255',
            'fax' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'email2' => 'nullable|string|max:255',
            'email3' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:255',
            'siret' => 'nullable|string|max:255',
        ]);

        $item = Section::create([
            'S_ID' => $validated['S_ID'] ?? null,
            'chef' => $validated['chef'] ?? null,
            'adjoint' => $validated['adjoint'] ?? null,
            'cadre' => $validated['cadre'] ?? null,
            'description' => $validated['description'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'code' => $validated['code'] ?? null,
            'nom' => $validated['nom'] ?? null,
            'address_complement' => $validated['address_complement'] ?? null,
            'zipcode' => $validated['zipcode'] ?? null,
            'city' => $validated['city'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'phone2' => $validated['phone2'] ?? null,
            'phone3' => $validated['phone3'] ?? null,
            'fax' => $validated['fax'] ?? null,
            'email' => $validated['email'] ?? null,
            'email2' => $validated['email2'] ?? null,
            'email3' => $validated['email3'] ?? null,
            'url' => $validated['url'] ?? null,
            'siret' => $validated['siret'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.ins_section.edit', $item->id)
            ->with('success', 'Section created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Section::findOrFail($id);

        $validated = $request->validate([
            'S_ID' => 'nullable|string|max:255',
            'chef' => 'nullable|string|max:255',
            'adjoint' => 'nullable|string|max:255',
            'cadre' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:255',
            'nom' => 'nullable|string|max:255',
            'address_complement' => 'nullable|string|max:255',
            'zipcode' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'phone2' => 'nullable|string|max:255',
            'phone3' => 'nullable|string|max:255',
            'fax' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'email2' => 'nullable|string|max:255',
            'email3' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:255',
            'siret' => 'nullable|string|max:255',
        ]);

        $item->update([
            'S_ID' => $validated['S_ID'] ?? null,
            'chef' => $validated['chef'] ?? null,
            'adjoint' => $validated['adjoint'] ?? null,
            'cadre' => $validated['cadre'] ?? null,
            'description' => $validated['description'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'code' => $validated['code'] ?? null,
            'nom' => $validated['nom'] ?? null,
            'address_complement' => $validated['address_complement'] ?? null,
            'zipcode' => $validated['zipcode'] ?? null,
            'city' => $validated['city'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'phone2' => $validated['phone2'] ?? null,
            'phone3' => $validated['phone3'] ?? null,
            'fax' => $validated['fax'] ?? null,
            'email' => $validated['email'] ?? null,
            'email2' => $validated['email2'] ?? null,
            'email3' => $validated['email3'] ?? null,
            'url' => $validated['url'] ?? null,
            'siret' => $validated['siret'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.ins_section.edit', $item->id)
            ->with('success', 'Section updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Section::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.ins_section.index')
            ->with('success', 'Section deleted successfully');
    }
                
}
