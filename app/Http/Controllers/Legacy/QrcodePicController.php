<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\QrcodePic;
use Illuminate\Http\Request;

/**
 * Legacy migration source: qrcode_pic.php
 * Legacy pattern: generic
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class QrcodePicController extends Controller
{
    public function index(Request $request)
    {
        $query = QrcodePic::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.qrcode_pic.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.qrcode_pic.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = QrcodePic::findOrFail($id);

        return view('legacy_migrated.qrcode_pic.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = QrcodePic::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.qrcode_pic.edit', $item->id)
            ->with('success', 'QrcodePic created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = QrcodePic::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.qrcode_pic.edit', $item->id)
            ->with('success', 'QrcodePic updated successfully');
    }
                

    public function destroy($id)
    {
        $item = QrcodePic::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.qrcode_pic.index')
            ->with('success', 'QrcodePic deleted successfully');
    }
                
}
