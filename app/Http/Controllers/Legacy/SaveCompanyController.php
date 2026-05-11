<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

/**
 * Legacy migration source: save_company.php
 * Legacy pattern: save
 * Legacy permission id: 29
 * This file stems from a legacy migration and requires functional verification.
 */
class SaveCompanyController extends Controller
{
    public function create()
    {
        return view('legacy_migrated.save_company.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Company::findOrFail($id);

        return view('legacy_migrated.save_company.form', [
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

        return redirect()->route('legacy_migrated.save_company.edit', $item->id)
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

        return redirect()->route('legacy_migrated.save_company.edit', $item->id)
            ->with('success', 'Company updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Company::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.save_company.index')
            ->with('success', 'Company deleted successfully');
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
