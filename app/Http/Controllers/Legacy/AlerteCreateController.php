<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\AlerteCreate;
use Illuminate\Http\Request;

/**
 * Legacy migration source: alerte_create.php
 * Legacy pattern: list
 * Legacy permission id: 43
 * This file stems from a legacy migration and requires functional verification.
 */
class AlerteCreateController extends Controller
{
    public function index(Request $request)
    {
        $query = AlerteCreate::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_id', 'like', '%' . $term . '%');
                $query->orWhere('count1', 'like', '%' . $term . '%');
                $query->orWhere('value', 'like', '%' . $term . '%');
                $query->orWhere('valueifhighestsectionhighestsectionmysectionifcheck_rights_sessionid', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.alerte_create.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.alerte_create.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = AlerteCreate::findOrFail($id);

        return view('legacy_migrated.alerte_create.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'menu1' => 'nullable|string|max:255',
            'menu3' => 'nullable|string|max:255',
            'comptage' => 'nullable|string|max:255',
            'maxchar' => 'nullable|string|max:255',
            'mode' => 'nullable|string|max:255',
            'mymessage' => 'nullable|string|max:255',
            'menu2' => 'nullable|string|max:255',
        ]);

        $item = AlerteCreate::create([
            'menu1' => $validated['menu1'] ?? null,
            'menu3' => $validated['menu3'] ?? null,
            'comptage' => $validated['comptage'] ?? null,
            'maxchar' => $validated['maxchar'] ?? null,
            'mode' => $validated['mode'] ?? null,
            'mymessage' => $validated['mymessage'] ?? null,
            'menu2' => $validated['menu2'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.alerte_create.edit', $item->id)
            ->with('success', 'AlerteCreate created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = AlerteCreate::findOrFail($id);

        $validated = $request->validate([
            'menu1' => 'nullable|string|max:255',
            'menu3' => 'nullable|string|max:255',
            'comptage' => 'nullable|string|max:255',
            'maxchar' => 'nullable|string|max:255',
            'mode' => 'nullable|string|max:255',
            'mymessage' => 'nullable|string|max:255',
            'menu2' => 'nullable|string|max:255',
        ]);

        $item->update([
            'menu1' => $validated['menu1'] ?? null,
            'menu3' => $validated['menu3'] ?? null,
            'comptage' => $validated['comptage'] ?? null,
            'maxchar' => $validated['maxchar'] ?? null,
            'mode' => $validated['mode'] ?? null,
            'mymessage' => $validated['mymessage'] ?? null,
            'menu2' => $validated['menu2'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.alerte_create.edit', $item->id)
            ->with('success', 'AlerteCreate updated successfully');
    }
                

    public function destroy($id)
    {
        $item = AlerteCreate::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.alerte_create.index')
            ->with('success', 'AlerteCreate deleted successfully');
    }
                
}
