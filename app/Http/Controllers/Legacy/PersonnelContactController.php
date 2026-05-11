<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\PersonnelContact;
use Illuminate\Http\Request;

/**
 * Legacy migration source: personnel_contact.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class PersonnelContactController extends Controller
{
    public function index(Request $request)
    {
        $query = PersonnelContact::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('ct_id', 'like', '%' . $term . '%');
                $query->orWhere('contact_type', 'like', '%' . $term . '%');
                $query->orWhere('ct_icon', 'like', '%' . $term . '%');
                $query->orWhere('contact_value', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.personnel_contact.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.personnel_contact.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = PersonnelContact::findOrFail($id);

        return view('legacy_migrated.personnel_contact.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'person' => 'nullable|string|max:255',
            'c' => 'nullable|string|max:255',
        ]);

        $item = PersonnelContact::create([
            'person' => $validated['person'] ?? null,
            'c' => $validated['c'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.personnel_contact.edit', $item->id)
            ->with('success', 'PersonnelContact created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = PersonnelContact::findOrFail($id);

        $validated = $request->validate([
            'person' => 'nullable|string|max:255',
            'c' => 'nullable|string|max:255',
        ]);

        $item->update([
            'person' => $validated['person'] ?? null,
            'c' => $validated['c'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.personnel_contact.edit', $item->id)
            ->with('success', 'PersonnelContact updated successfully');
    }
                

    public function destroy($id)
    {
        $item = PersonnelContact::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.personnel_contact.index')
            ->with('success', 'PersonnelContact deleted successfully');
    }
                
}
