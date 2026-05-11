<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Wizard;
use Illuminate\Http\Request;

/**
 * Legacy migration source: wizard.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class WizardController extends Controller
{
    public function index(Request $request)
    {
        $query = Wizard::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.wizard.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.wizard.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Wizard::findOrFail($id);

        return view('legacy_migrated.wizard.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cisname' => 'nullable|string|max:255',
            'organisation_name' => 'nullable|string|max:255',
            'cisurl' => 'nullable|string|max:255',
            'admin_email' => 'nullable|string|max:255',
            'application_title' => 'nullable|string|max:255',
            'type_organisation' => 'nullable|string|max:255',
        ]);

        $item = Wizard::create([
            'cisname' => $validated['cisname'] ?? null,
            'organisation_name' => $validated['organisation_name'] ?? null,
            'cisurl' => $validated['cisurl'] ?? null,
            'admin_email' => $validated['admin_email'] ?? null,
            'application_title' => $validated['application_title'] ?? null,
            'type_organisation' => $validated['type_organisation'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.wizard.edit', $item->id)
            ->with('success', 'Wizard created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Wizard::findOrFail($id);

        $validated = $request->validate([
            'cisname' => 'nullable|string|max:255',
            'organisation_name' => 'nullable|string|max:255',
            'cisurl' => 'nullable|string|max:255',
            'admin_email' => 'nullable|string|max:255',
            'application_title' => 'nullable|string|max:255',
            'type_organisation' => 'nullable|string|max:255',
        ]);

        $item->update([
            'cisname' => $validated['cisname'] ?? null,
            'organisation_name' => $validated['organisation_name'] ?? null,
            'cisurl' => $validated['cisurl'] ?? null,
            'admin_email' => $validated['admin_email'] ?? null,
            'application_title' => $validated['application_title'] ?? null,
            'type_organisation' => $validated['type_organisation'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.wizard.edit', $item->id)
            ->with('success', 'Wizard updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Wizard::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.wizard.index')
            ->with('success', 'Wizard deleted successfully');
    }
                
}
