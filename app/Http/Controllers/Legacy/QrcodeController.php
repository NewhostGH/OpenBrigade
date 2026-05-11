<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Qrcode;
use Illuminate\Http\Request;

/**
 * Legacy migration source: qrcode.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class QrcodeController extends Controller
{
    public function index(Request $request)
    {
        $query = Qrcode::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.qrcode.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.qrcode.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Qrcode::findOrFail($id);

        return view('legacy_migrated.qrcode.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = Qrcode::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.qrcode.edit', $item->id)
            ->with('success', 'Qrcode created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Qrcode::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.qrcode.edit', $item->id)
            ->with('success', 'Qrcode updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Qrcode::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.qrcode.index')
            ->with('success', 'Qrcode deleted successfully');
    }
                
}
