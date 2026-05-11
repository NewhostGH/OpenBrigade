<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use Illuminate\Http\Request;

/**
 * Legacy migration source: chat_message.php
 * Legacy pattern: list
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class ChatMessageController extends Controller
{
    public function index(Request $request)
    {
        $query = ChatMessage::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('count', 'like', '%' . $term . '%');
                $query->orWhere('date_formatnow', 'like', '%' . $term . '%');
                $query->orWhere('hisc_date', 'like', '%' . $term . '%');
                $query->orWhere('p_id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.chat_message.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.chat_message.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = ChatMessage::findOrFail($id);

        return view('legacy_migrated.chat_message.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = ChatMessage::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.chat_message.edit', $item->id)
            ->with('success', 'ChatMessage created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = ChatMessage::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.chat_message.edit', $item->id)
            ->with('success', 'ChatMessage updated successfully');
    }
                

    public function destroy($id)
    {
        $item = ChatMessage::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.chat_message.index')
            ->with('success', 'ChatMessage deleted successfully');
    }
                
}
