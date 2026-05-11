<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\MailCreateInput;
use Illuminate\Http\Request;

/**
 * Legacy migration source: mail_create_input.php
 * Legacy pattern: list
 * Legacy permission id: 43
 * This file stems from a legacy migration and requires functional verification.
 */
class MailCreateInputController extends Controller
{
    public function index(Request $request)
    {
        $query = MailCreateInput::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
                $query->orWhere('1', 'like', '%' . $term . '%');
                $query->orWhere('2', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.mail_create_input.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.mail_create_input.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = MailCreateInput::findOrFail($id);

        return view('legacy_migrated.mail_create_input.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = MailCreateInput::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.mail_create_input.edit', $item->id)
            ->with('success', 'MailCreateInput created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = MailCreateInput::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.mail_create_input.edit', $item->id)
            ->with('success', 'MailCreateInput updated successfully');
    }
                

    public function destroy($id)
    {
        $item = MailCreateInput::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.mail_create_input.index')
            ->with('success', 'MailCreateInput deleted successfully');
    }
                
}
