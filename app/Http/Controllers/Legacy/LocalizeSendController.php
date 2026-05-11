<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\LocalizeSend;
use Illuminate\Http\Request;

/**
 * Legacy migration source: localize_send.php
 * Legacy pattern: generic
 * Legacy permission id: 23
 * This file stems from a legacy migration and requires functional verification.
 */
class LocalizeSendController extends Controller
{
    public function index(Request $request)
    {
        $query = LocalizeSend::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('d_secret', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.localize_send.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.localize_send.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = LocalizeSend::findOrFail($id);

        return view('legacy_migrated.localize_send.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = LocalizeSend::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.localize_send.edit', $item->id)
            ->with('success', 'LocalizeSend created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = LocalizeSend::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.localize_send.edit', $item->id)
            ->with('success', 'LocalizeSend updated successfully');
    }
                

    public function destroy($id)
    {
        $item = LocalizeSend::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.localize_send.index')
            ->with('success', 'LocalizeSend deleted successfully');
    }
                
}
