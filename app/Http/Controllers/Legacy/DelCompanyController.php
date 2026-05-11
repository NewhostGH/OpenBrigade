<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

/**
 * Legacy migration source: del_company.php
 * Legacy pattern: delete
 * Legacy permission id: 19
 * This file stems from a legacy migration and requires functional verification.
 */
class DelCompanyController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.del_company.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Company::findOrFail($id);

        return view('legacy_migrated.del_company.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Company::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_company.edit', $item->id)
            ->with('success', 'Company created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Company::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.del_company.edit', $item->id)
            ->with('success', 'Company updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Company::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.del_company.index')
            ->with('success', 'Company deleted successfully');
    }
                
}
