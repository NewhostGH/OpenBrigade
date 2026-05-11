<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Phpinfo;
use Illuminate\Http\Request;

/**
 * Legacy migration source: phpinfo.php
 * Legacy pattern: generic
 * Legacy permission id: 14
 * This file stems from a legacy migration and requires functional verification.
 */
class PhpinfoController extends Controller
{
    public function index(Request $request)
    {
        $query = Phpinfo::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.phpinfo.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.phpinfo.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Phpinfo::findOrFail($id);

        return view('legacy_migrated.phpinfo.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Phpinfo::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.phpinfo.edit', $item->id)
            ->with('success', 'Phpinfo created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Phpinfo::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.phpinfo.edit', $item->id)
            ->with('success', 'Phpinfo updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Phpinfo::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.phpinfo.index')
            ->with('success', 'Phpinfo deleted successfully');
    }
                
}
