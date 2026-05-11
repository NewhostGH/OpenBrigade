<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\PersonnelLoad;
use Illuminate\Http\Request;

/**
 * Legacy migration source: personnel_load.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class PersonnelLoadController extends Controller
{
    public function index(Request $request)
    {
        $query = PersonnelLoad::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }
        if (session()->has('SES_COMPANY')) {
            $query->where('company_id', session('SES_COMPANY'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.personnel_load.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.personnel_load.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = PersonnelLoad::findOrFail($id);

        return view('legacy_migrated.personnel_load.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'SendMail' => 'nullable|string|max:255',
        ]);

        $item = PersonnelLoad::create([
            'SendMail' => $validated['SendMail'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.personnel_load.edit', $item->id)
            ->with('success', 'PersonnelLoad created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = PersonnelLoad::findOrFail($id);

        $validated = $request->validate([
            'SendMail' => 'nullable|string|max:255',
        ]);

        $item->update([
            'SendMail' => $validated['SendMail'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.personnel_load.edit', $item->id)
            ->with('success', 'PersonnelLoad updated successfully');
    }
                

    public function destroy($id)
    {
        $item = PersonnelLoad::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.personnel_load.index')
            ->with('success', 'PersonnelLoad deleted successfully');
    }
                
}
