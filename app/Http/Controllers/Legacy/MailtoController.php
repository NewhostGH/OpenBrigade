<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Mailto;
use Illuminate\Http\Request;

/**
 * Legacy migration source: mailto.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class MailtoController extends Controller
{
    public function index(Request $request)
    {
        $query = Mailto::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('e_libelle', 'like', '%' . $term . '%');
                $query->orWhere('e_whatsapp', 'like', '%' . $term . '%');
                $query->orWhere('p_email', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.mailto.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.mailto.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Mailto::findOrFail($id);

        return view('legacy_migrated.mailto.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Mailto::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.mailto.edit', $item->id)
            ->with('success', 'Mailto created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Mailto::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.mailto.edit', $item->id)
            ->with('success', 'Mailto updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Mailto::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.mailto.index')
            ->with('success', 'Mailto deleted successfully');
    }
                
}
