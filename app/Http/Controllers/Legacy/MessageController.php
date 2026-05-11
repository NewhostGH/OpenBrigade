<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;

/**
 * Legacy migration source: message.php
 * Legacy pattern: list
 * Legacy permission id: 44
 * This file stems from a legacy migration and requires functional verification.
 */
class MessageController extends Controller
{
    public function index(Request $request)
    {
        $query = Message::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('maxm_id1', 'like', '%' . $term . '%');
                $query->orWhere('now', 'like', '%' . $term . '%');
                $query->orWhere('p_email', 'like', '%' . $term . '%');
                $query->orWhere('nametm_idclassformcontrolselectcontroldatastylebtndefaultqueryselecttm_id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.message.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.message.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = Message::findOrFail($id);

        return view('legacy_migrated.message.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'catmessage' => 'nullable|string|max:255',
            'mail' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
            'objet' => 'nullable|string|max:255',
            'userfile[]' => 'nullable|file',
            'annuler' => 'nullable|string|max:255',
            'tab' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'search' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:255',
            'TM_ID' => 'nullable|string|max:255',
            'duree' => 'nullable|string|max:255',
        ]);

        $item = Message::create([
            'catmessage' => $validated['catmessage'] ?? null,
            'mail' => $validated['mail'] ?? null,
            'section' => $validated['section'] ?? null,
            'objet' => $validated['objet'] ?? null,
            'userfile[]' => $validated['userfile[]'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
            'tab' => $validated['tab'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'search' => $validated['search'] ?? null,
            'message' => $validated['message'] ?? null,
            'TM_ID' => $validated['TM_ID'] ?? null,
            'duree' => $validated['duree'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.message.edit', $item->id)
            ->with('success', 'Message created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = Message::findOrFail($id);

        $validated = $request->validate([
            'catmessage' => 'nullable|string|max:255',
            'mail' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
            'objet' => 'nullable|string|max:255',
            'userfile[]' => 'nullable|file',
            'annuler' => 'nullable|string|max:255',
            'tab' => 'nullable|string|max:255',
            'filter' => 'nullable|string|max:255',
            'search' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:255',
            'TM_ID' => 'nullable|string|max:255',
            'duree' => 'nullable|string|max:255',
        ]);

        $item->update([
            'catmessage' => $validated['catmessage'] ?? null,
            'mail' => $validated['mail'] ?? null,
            'section' => $validated['section'] ?? null,
            'objet' => $validated['objet'] ?? null,
            'userfile[]' => $validated['userfile[]'] ?? null,
            'annuler' => $validated['annuler'] ?? null,
            'tab' => $validated['tab'] ?? null,
            'filter' => $validated['filter'] ?? null,
            'search' => $validated['search'] ?? null,
            'message' => $validated['message'] ?? null,
            'TM_ID' => $validated['TM_ID'] ?? null,
            'duree' => $validated['duree'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.message.edit', $item->id)
            ->with('success', 'Message updated successfully');
    }
                

    public function destroy($id)
    {
        $item = Message::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.message.index')
            ->with('success', 'Message deleted successfully');
    }
                
}
