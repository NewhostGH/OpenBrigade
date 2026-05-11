<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\CompanyRole;
use Illuminate\Http\Request;

/**
 * Legacy migration source: upd_company_role.php
 * Legacy pattern: edit
 * Legacy permission id: 37
 * This file stems from a legacy migration and requires functional verification.
 */
class UpdCompanyRoleController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.upd_company_role.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = CompanyRole::findOrFail($id);

        return view('legacy_migrated.upd_company_role.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'resp' => 'nullable|string|max:255',
        ]);

        $item = CompanyRole::create([
            'resp' => $validated['resp'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_company_role.edit', $item->id)
            ->with('success', 'CompanyRole created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = CompanyRole::findOrFail($id);

        $validated = $request->validate([
            'resp' => 'nullable|string|max:255',
        ]);

        $item->update([
            'resp' => $validated['resp'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.upd_company_role.edit', $item->id)
            ->with('success', 'CompanyRole updated successfully');
    }
                

    public function destroy($id)
    {
        $item = CompanyRole::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.upd_company_role.index')
            ->with('success', 'CompanyRole deleted successfully');
    }
                
}
