<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use Illuminate\Http\Request;

/**
 * Legacy migration source: audit.php
 * Legacy pattern: generic
 * Legacy permission id: 20
 * This file stems from a legacy migration and requires functional verification.
 */
class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = Audit::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('nom', 'like', '%' . $term . '%');
                $query->orWhere('section', 'like', '%' . $term . '%');
                $query->orWhere('date_connexion', 'like', '%' . $term . '%');
                $query->orWhere('dernire_action', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.audit.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.audit.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Audit::findOrFail($id);

        return view('legacy_migrated.audit.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
        ]);

        $item = Audit::create([
            'sub' => $validated['sub'] ?? null,
            'filter' => $validated['filter'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.audit.edit', $item->id)
            ->with('success', 'Audit created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Audit::findOrFail($id);

        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
        ]);

        $item->update([
            'sub' => $validated['sub'] ?? null,
            'filter' => $validated['filter'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.audit.edit', $item->id)
            ->with('success', 'Audit updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Audit::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.audit.index')
            ->with('success', 'Audit deleted successfully');
    }
                
}
