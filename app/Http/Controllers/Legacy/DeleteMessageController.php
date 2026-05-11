<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\DeleteMessage;
use Illuminate\Http\Request;

/**
 * Legacy migration source: delete_message.php
 * Legacy pattern: generic
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class DeleteMessageController extends Controller
{
    public function index(Request $request)
    {
        $query = DeleteMessage::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_id', 'like', '%' . $term . '%');
                $query->orWhere('date_formatm_date', 'like', '%' . $term . '%');
                $query->orWhere('dmym_date', 'like', '%' . $term . '%');
                $query->orWhere('m_objet', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.delete_message.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.delete_message.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = DeleteMessage::findOrFail($id);

        return view('legacy_migrated.delete_message.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = DeleteMessage::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.delete_message.edit', $item->id)
            ->with('success', 'DeleteMessage created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = DeleteMessage::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.delete_message.edit', $item->id)
            ->with('success', 'DeleteMessage updated successfully');
    }
                

    public function destroy($id)
    {
        $item = DeleteMessage::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.delete_message.index')
            ->with('success', 'DeleteMessage deleted successfully');
    }
                
}
