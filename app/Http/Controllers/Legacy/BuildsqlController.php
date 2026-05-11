<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Buildsql;
use Illuminate\Http\Request;

/**
 * Legacy migration source: buildsql.php
 * Legacy pattern: generic
 * Legacy permission id: 14
 * This file stems from a legacy migration and requires functional verification.
 */
class BuildsqlController extends Controller
{
    public function index(Request $request)
    {
        $query = Buildsql::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.buildsql.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.buildsql.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Buildsql::findOrFail($id);

        return view('legacy_migrated.buildsql.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Buildsql::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.buildsql.edit', $item->id)
            ->with('success', 'Buildsql created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Buildsql::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.buildsql.edit', $item->id)
            ->with('success', 'Buildsql updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Buildsql::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.buildsql.index')
            ->with('success', 'Buildsql deleted successfully');
    }
                
}
