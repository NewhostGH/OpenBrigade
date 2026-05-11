<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Documents;
use Illuminate\Http\Request;

/**
 * Legacy migration source: documents.php
 * Legacy pattern: list
 * Legacy permission id: 44
 * This file stems from a legacy migration and requires functional verification.
 */
class DocumentsController extends Controller
{
    public function index(Request $request)
    {
        $query = Documents::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('value', 'like', '%' . $term . '%');
                $query->orWhere('datastylebtndefaultdatacontainerbodylevelget_levelfiltermycolorget_color_levellevelclassstylebackgroundmycolordisplay_children21', 'like', '%' . $term . '%');
                $query->orWhere('0', 'like', '%' . $term . '%');
                $query->orWhere('filter', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.documents.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.documents.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Documents::findOrFail($id);

        return view('legacy_migrated.documents.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'section' => 'nullable|string|max:255',
            'goup' => 'nullable|string|max:255',
            'yeardoc' => 'nullable|string|max:255',
            'td' => 'nullable|string|max:255',
        ]);

        $item = Documents::create([
            'section' => $validated['section'] ?? null,
            'goup' => $validated['goup'] ?? null,
            'yeardoc' => $validated['yeardoc'] ?? null,
            'td' => $validated['td'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.documents.edit', $item->id)
            ->with('success', 'Documents created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Documents::findOrFail($id);

        $validated = $request->validate([
            'section' => 'nullable|string|max:255',
            'goup' => 'nullable|string|max:255',
            'yeardoc' => 'nullable|string|max:255',
            'td' => 'nullable|string|max:255',
        ]);

        $item->update([
            'section' => $validated['section'] ?? null,
            'goup' => $validated['goup'] ?? null,
            'yeardoc' => $validated['yeardoc'] ?? null,
            'td' => $validated['td'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.documents.edit', $item->id)
            ->with('success', 'Documents updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Documents::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.documents.index')
            ->with('success', 'Documents deleted successfully');
    }
                
}
