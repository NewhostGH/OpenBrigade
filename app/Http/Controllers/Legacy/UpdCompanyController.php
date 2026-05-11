<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

/**
 * Legacy migration source: upd_company.php
 * Legacy pattern: edit
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class UpdCompanyController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.upd_company.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Company::findOrFail($id);

        return view('legacy_migrated.upd_company.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'operation' => 'nullable|string|max:255',
            'C_ID' => 'nullable|string|max:255',
            'from' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'C_NAME' => 'nullable|string|max:255',
            'groupe' => 'nullable|string|max:255',
            'C_DESCRIPTION' => 'nullable|string|max:255',
            'C_SIRET' => 'nullable|string|max:255',
            'zipcode' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'relation_nom' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'fax' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'Annuler' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'TC_CODE' => 'nullable|string|max:255',
            'parent' => 'nullable|string|max:255',
        ]);

        $item = Company::create([
            'operation' => $validated['operation'] ?? null,
            'C_ID' => $validated['C_ID'] ?? null,
            'from' => $validated['from'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'C_NAME' => $validated['C_NAME'] ?? null,
            'groupe' => $validated['groupe'] ?? null,
            'C_DESCRIPTION' => $validated['C_DESCRIPTION'] ?? null,
            'C_SIRET' => $validated['C_SIRET'] ?? null,
            'zipcode' => $validated['zipcode'] ?? null,
            'city' => $validated['city'] ?? null,
            'relation_nom' => $validated['relation_nom'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'fax' => $validated['fax'] ?? null,
            'email' => $validated['email'] ?? null,
            'Annuler' => $validated['Annuler'] ?? null,
            'address' => $validated['address'] ?? null,
            'TC_CODE' => $validated['TC_CODE'] ?? null,
            'parent' => $validated['parent'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_company.edit', $item->id)
            ->with('success', 'Company created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Company::findOrFail($id);

        $validated = $request->validate([
            'operation' => 'nullable|string|max:255',
            'C_ID' => 'nullable|string|max:255',
            'from' => 'nullable|string|max:255',
            'evenement' => 'nullable|string|max:255',
            'C_NAME' => 'nullable|string|max:255',
            'groupe' => 'nullable|string|max:255',
            'C_DESCRIPTION' => 'nullable|string|max:255',
            'C_SIRET' => 'nullable|string|max:255',
            'zipcode' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'relation_nom' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'fax' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'Annuler' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'TC_CODE' => 'nullable|string|max:255',
            'parent' => 'nullable|string|max:255',
        ]);

        $item->update([
            'operation' => $validated['operation'] ?? null,
            'C_ID' => $validated['C_ID'] ?? null,
            'from' => $validated['from'] ?? null,
            'evenement' => $validated['evenement'] ?? null,
            'C_NAME' => $validated['C_NAME'] ?? null,
            'groupe' => $validated['groupe'] ?? null,
            'C_DESCRIPTION' => $validated['C_DESCRIPTION'] ?? null,
            'C_SIRET' => $validated['C_SIRET'] ?? null,
            'zipcode' => $validated['zipcode'] ?? null,
            'city' => $validated['city'] ?? null,
            'relation_nom' => $validated['relation_nom'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'fax' => $validated['fax'] ?? null,
            'email' => $validated['email'] ?? null,
            'Annuler' => $validated['Annuler'] ?? null,
            'address' => $validated['address'] ?? null,
            'TC_CODE' => $validated['TC_CODE'] ?? null,
            'parent' => $validated['parent'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_company.edit', $item->id)
            ->with('success', 'Company updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Company::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.upd_company.index')
            ->with('success', 'Company deleted successfully');
    }
                
}
