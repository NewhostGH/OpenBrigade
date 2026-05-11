<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Qualifications;
use Illuminate\Http\Request;

/**
 * Legacy migration source: qualifications.php
 * Legacy pattern: list
 * Legacy permission id: 56
 * This file stems from a legacy migration and requires functional verification.
 */
class QualificationsController extends Controller
{
    public function index(Request $request)
    {
        $query = Qualifications::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('ps_id', 'like', '%' . $term . '%');
                $query->orWhere('type', 'like', '%' . $term . '%');
                $query->orWhere('ps_expirable', 'like', '%' . $term . '%');
                $query->orWhere('days_warning', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.qualifications.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.qualifications.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Qualifications::findOrFail($id);

        return view('legacy_migrated.qualifications.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'typequalif' => 'nullable|string|max:255',
            'pompier' => 'nullable|string|max:255',
            'order' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'from' => 'nullable|string|max:255',
            'sub' => 'nullable|string|max:255',
            'competence' => 'nullable|string|max:255',
            '$P_ID' => 'nullable|string|max:255',
            'exp_' => 'nullable|string|max:255',
            'updated_' => 'nullable|string|max:255',
            '$PS_ID' => 'nullable|string|max:255',
            'Retour' => 'nullable|string|max:255',
            'filter_one' => 'nullable|string|max:255',
        ]);

        $item = Qualifications::create([
            'typequalif' => $validated['typequalif'] ?? null,
            'pompier' => $validated['pompier'] ?? null,
            'order' => $validated['order'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'from' => $validated['from'] ?? null,
            'sub' => $validated['sub'] ?? null,
            'competence' => $validated['competence'] ?? null,
            '$P_ID' => $validated['$P_ID'] ?? null,
            'exp_' => $validated['exp_'] ?? null,
            'updated_' => $validated['updated_'] ?? null,
            '$PS_ID' => $validated['$PS_ID'] ?? null,
            'Retour' => $validated['Retour'] ?? null,
            'filter_one' => $validated['filter_one'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.qualifications.edit', $item->id)
            ->with('success', 'Qualifications created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Qualifications::findOrFail($id);

        $validated = $request->validate([
            'typequalif' => 'nullable|string|max:255',
            'pompier' => 'nullable|string|max:255',
            'order' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'from' => 'nullable|string|max:255',
            'sub' => 'nullable|string|max:255',
            'competence' => 'nullable|string|max:255',
            '$P_ID' => 'nullable|string|max:255',
            'exp_' => 'nullable|string|max:255',
            'updated_' => 'nullable|string|max:255',
            '$PS_ID' => 'nullable|string|max:255',
            'Retour' => 'nullable|string|max:255',
            'filter_one' => 'nullable|string|max:255',
        ]);

        $item->update([
            'typequalif' => $validated['typequalif'] ?? null,
            'pompier' => $validated['pompier'] ?? null,
            'order' => $validated['order'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'from' => $validated['from'] ?? null,
            'sub' => $validated['sub'] ?? null,
            'competence' => $validated['competence'] ?? null,
            '$P_ID' => $validated['$P_ID'] ?? null,
            'exp_' => $validated['exp_'] ?? null,
            'updated_' => $validated['updated_'] ?? null,
            '$PS_ID' => $validated['$PS_ID'] ?? null,
            'Retour' => $validated['Retour'] ?? null,
            'filter_one' => $validated['filter_one'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.qualifications.edit', $item->id)
            ->with('success', 'Qualifications updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Qualifications::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.qualifications.index')
            ->with('success', 'Qualifications deleted successfully');
    }
                
}
