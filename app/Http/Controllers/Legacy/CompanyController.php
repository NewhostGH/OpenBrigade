<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

/**
 * Legacy migration source: company.php
 * Legacy pattern: list
 * Legacy permission id: 29
 * This file stems from a legacy migration and requires functional verification.
 */
class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('c_id', 'like', '%' . $term . '%');
                $query->orWhere('tc_code', 'like', '%' . $term . '%');
                $query->orWhere('c_name', 'like', '%' . $term . '%');
                $query->orWhere('s_id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.company.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.company.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Company::findOrFail($id);

        return view('legacy_migrated.company.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'lib' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'typecompany' => 'nullable|string|max:255',
        ]);

        $item = Company::create([
            'sub' => $validated['sub'] ?? null,
            'lib' => $validated['lib'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'typecompany' => $validated['typecompany'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.company.edit', $item->id)
            ->with('success', 'Company created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Company::findOrFail($id);

        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'lib' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'typecompany' => 'nullable|string|max:255',
        ]);

        $item->update([
            'sub' => $validated['sub'] ?? null,
            'lib' => $validated['lib'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'typecompany' => $validated['typecompany'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.company.edit', $item->id)
            ->with('success', 'Company updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Company::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.company.index')
            ->with('success', 'Company deleted successfully');
    }
                
}
