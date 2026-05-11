<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Http\Request;

/**
 * Legacy migration source: upd_section.php
 * Legacy pattern: edit
 * Legacy permission id: 44
 * This file stems from a legacy migration and requires functional verification.
 */
class UpdSectionController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.upd_section.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Section::findOrFail($id);

        return view('legacy_migrated.upd_section.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'SMS_LOCAL_USER' => 'nullable|string|max:255',
            'SMS_LOCAL_PASSWORD' => 'nullable|string|max:255',
            'SMS_LOCAL_API_ID' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'S_ID' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:255',
            'nom' => 'nullable|string|max:255',
            'parent' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'phone2' => 'nullable|string|max:255',
            'phone3' => 'nullable|string|max:255',
            'fax' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'email2' => 'nullable|string|max:255',
            'email3' => 'nullable|string|max:255',
            'whatsapp_group' => 'nullable|string|max:255',
            'rad1' => 'nullable|string|max:255',
            'rad2' => 'nullable|string|max:255',
            'address_complement' => 'nullable|string|max:255',
        ]);

        $item = Section::create([
            'SMS_LOCAL_USER' => $validated['SMS_LOCAL_USER'] ?? null,
            'SMS_LOCAL_PASSWORD' => $validated['SMS_LOCAL_PASSWORD'] ?? null,
            'SMS_LOCAL_API_ID' => $validated['SMS_LOCAL_API_ID'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'S_ID' => $validated['S_ID'] ?? null,
            'status' => $validated['status'] ?? null,
            'code' => $validated['code'] ?? null,
            'nom' => $validated['nom'] ?? null,
            'parent' => $validated['parent'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'phone2' => $validated['phone2'] ?? null,
            'phone3' => $validated['phone3'] ?? null,
            'fax' => $validated['fax'] ?? null,
            'email' => $validated['email'] ?? null,
            'email2' => $validated['email2'] ?? null,
            'email3' => $validated['email3'] ?? null,
            'whatsapp_group' => $validated['whatsapp_group'] ?? null,
            'rad1' => $validated['rad1'] ?? null,
            'rad2' => $validated['rad2'] ?? null,
            'address_complement' => $validated['address_complement'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_section.edit', $item->id)
            ->with('success', 'Section created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Section::findOrFail($id);

        $validated = $request->validate([
            'SMS_LOCAL_USER' => 'nullable|string|max:255',
            'SMS_LOCAL_PASSWORD' => 'nullable|string|max:255',
            'SMS_LOCAL_API_ID' => 'nullable|string|max:255',
            'operation' => 'nullable|string|max:255',
            'S_ID' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:255',
            'nom' => 'nullable|string|max:255',
            'parent' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'phone2' => 'nullable|string|max:255',
            'phone3' => 'nullable|string|max:255',
            'fax' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'email2' => 'nullable|string|max:255',
            'email3' => 'nullable|string|max:255',
            'whatsapp_group' => 'nullable|string|max:255',
            'rad1' => 'nullable|string|max:255',
            'rad2' => 'nullable|string|max:255',
            'address_complement' => 'nullable|string|max:255',
        ]);

        $item->update([
            'SMS_LOCAL_USER' => $validated['SMS_LOCAL_USER'] ?? null,
            'SMS_LOCAL_PASSWORD' => $validated['SMS_LOCAL_PASSWORD'] ?? null,
            'SMS_LOCAL_API_ID' => $validated['SMS_LOCAL_API_ID'] ?? null,
            'operation' => $validated['operation'] ?? null,
            'S_ID' => $validated['S_ID'] ?? null,
            'status' => $validated['status'] ?? null,
            'code' => $validated['code'] ?? null,
            'nom' => $validated['nom'] ?? null,
            'parent' => $validated['parent'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'phone2' => $validated['phone2'] ?? null,
            'phone3' => $validated['phone3'] ?? null,
            'fax' => $validated['fax'] ?? null,
            'email' => $validated['email'] ?? null,
            'email2' => $validated['email2'] ?? null,
            'email3' => $validated['email3'] ?? null,
            'whatsapp_group' => $validated['whatsapp_group'] ?? null,
            'rad1' => $validated['rad1'] ?? null,
            'rad2' => $validated['rad2'] ?? null,
            'address_complement' => $validated['address_complement'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_section.edit', $item->id)
            ->with('success', 'Section updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Section::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.upd_section.index')
            ->with('success', 'Section deleted successfully');
    }
                
}
