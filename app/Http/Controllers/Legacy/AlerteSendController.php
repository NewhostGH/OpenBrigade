<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\AlerteSend;
use Illuminate\Http\Request;

/**
 * Legacy migration source: alerte_send.php
 * Legacy pattern: list
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class AlerteSendController extends Controller
{
    public function index(Request $request)
    {
        $query = AlerteSend::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_id', 'like', '%' . $term . '%');
                $query->orWhere('distinctp_id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.alerte_send.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.alerte_send.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = AlerteSend::findOrFail($id);

        return view('legacy_migrated.alerte_send.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = AlerteSend::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.alerte_send.edit', $item->id)
            ->with('success', 'AlerteSend created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = AlerteSend::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.alerte_send.edit', $item->id)
            ->with('success', 'AlerteSend updated successfully');
    }
                

    public function destroy($id)
    {
        $item = AlerteSend::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.alerte_send.index')
            ->with('success', 'AlerteSend deleted successfully');
    }
                
}
