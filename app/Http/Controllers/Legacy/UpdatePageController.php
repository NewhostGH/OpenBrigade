<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\UpdatePage;
use Illuminate\Http\Request;

/**
 * Legacy migration source: update_page.php
 * Legacy pattern: generic
 * Legacy permission id: 6
 * This file stems from a legacy migration and requires functional verification.
 */
class UpdatePageController extends Controller
{
    public function index(Request $request)
    {
        $query = UpdatePage::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('eq_jour', 'like', '%' . $term . '%');
                $query->orWhere('eq_nuit', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.update_page.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.update_page.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = UpdatePage::findOrFail($id);

        return view('legacy_migrated.update_page.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = UpdatePage::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.update_page.edit', $item->id)
            ->with('success', 'UpdatePage created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = UpdatePage::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.update_page.edit', $item->id)
            ->with('success', 'UpdatePage updated successfully');
    }
                

    public function destroy($id)
    {
        $item = UpdatePage::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.update_page.index')
            ->with('success', 'UpdatePage deleted successfully');
    }
                
}
