<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use Illuminate\Http\Request;

/**
 * Legacy migration source: chat.php
 * Legacy pattern: list
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class ChatController extends Controller
{
    public function index(Request $request)
    {
        $query = Chat::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_id', 'like', '%' . $term . '%');
                $query->orWhere('p_photo', 'like', '%' . $term . '%');
                $query->orWhere('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.chat.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.chat.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Chat::findOrFail($id);

        return view('legacy_migrated.chat.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'msg' => 'nullable|string|max:255',
            'sendMsg' => 'nullable|string|max:255',
        ]);

        $item = Chat::create([
            'msg' => $validated['msg'] ?? null,
            'sendMsg' => $validated['sendMsg'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.chat.edit', $item->id)
            ->with('success', 'Chat created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Chat::findOrFail($id);

        $validated = $request->validate([
            'msg' => 'nullable|string|max:255',
            'sendMsg' => 'nullable|string|max:255',
        ]);

        $item->update([
            'msg' => $validated['msg'] ?? null,
            'sendMsg' => $validated['sendMsg'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.chat.edit', $item->id)
            ->with('success', 'Chat updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Chat::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.chat.index')
            ->with('success', 'Chat deleted successfully');
    }
                
}
