<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\ReportCotisations;
use Illuminate\Http\Request;

/**
 * Legacy migration source: report_cotisations.php
 * Legacy pattern: list
 * Legacy permission id: 53
 * This file stems from a legacy migration and requires functional verification.
 */
class ReportCotisationsController extends Controller
{
    public function index(Request $request)
    {
        $query = ReportCotisations::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_profession', 'like', '%' . $term . '%');
                $query->orWhere('tp_id', 'like', '%' . $term . '%');
                $query->orWhere('montantsum', 'like', '%' . $term . '%');
                $query->orWhere('s_id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.report_cotisations.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.report_cotisations.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = ReportCotisations::findOrFail($id);

        return view('legacy_migrated.report_cotisations.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dtdb' => 'nullable|string|max:255',
            'dtfn' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
        ]);

        $item = ReportCotisations::create([
            'dtdb' => $validated['dtdb'] ?? null,
            'dtfn' => $validated['dtfn'] ?? null,
            'filter' => $validated['filter'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.report_cotisations.edit', $item->id)
            ->with('success', 'ReportCotisations created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = ReportCotisations::findOrFail($id);

        $validated = $request->validate([
            'dtdb' => 'nullable|string|max:255',
            'dtfn' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
        ]);

        $item->update([
            'dtdb' => $validated['dtdb'] ?? null,
            'dtfn' => $validated['dtfn'] ?? null,
            'filter' => $validated['filter'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.report_cotisations.edit', $item->id)
            ->with('success', 'ReportCotisations updated successfully');
    }
                

    public function destroy($id)
    {
        $item = ReportCotisations::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.report_cotisations.index')
            ->with('success', 'ReportCotisations deleted successfully');
    }
                
}
