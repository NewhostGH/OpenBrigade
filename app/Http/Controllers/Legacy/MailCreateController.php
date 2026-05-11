<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\MailCreate;
use Illuminate\Http\Request;

/**
 * Legacy migration source: mail_create.php
 * Legacy pattern: list
 * Legacy permission id: 43
 * This file stems from a legacy migration and requires functional verification.
 */
class MailCreateController extends Controller
{
    public function index(Request $request)
    {
        $query = MailCreate::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('p_id', 'like', '%' . $term . '%');
                $query->orWhere('upperp_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
                $query->orWhere('p_email', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.mail_create.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.mail_create.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = MailCreate::findOrFail($id);

        return view('legacy_migrated.mail_create.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'liste2' => 'nullable|string|max:255',
            'comptage' => 'nullable|string|max:255',
            'maxchar' => 'nullable|string|max:255',
            'mode' => 'nullable|string|max:255',
            'mymessage' => 'nullable|string|max:255',
            'SelectionMail' => 'nullable|string|max:255',
            'Messagesubject' => 'nullable|string|max:255',
            'Messagebody' => 'nullable|string|max:255',
        ]);

        $item = MailCreate::create([
            'liste2' => $validated['liste2'] ?? null,
            'comptage' => $validated['comptage'] ?? null,
            'maxchar' => $validated['maxchar'] ?? null,
            'mode' => $validated['mode'] ?? null,
            'mymessage' => $validated['mymessage'] ?? null,
            'SelectionMail' => $validated['SelectionMail'] ?? null,
            'Messagesubject' => $validated['Messagesubject'] ?? null,
            'Messagebody' => $validated['Messagebody'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.mail_create.edit', $item->id)
            ->with('success', 'MailCreate created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = MailCreate::findOrFail($id);

        $validated = $request->validate([
            'liste2' => 'nullable|string|max:255',
            'comptage' => 'nullable|string|max:255',
            'maxchar' => 'nullable|string|max:255',
            'mode' => 'nullable|string|max:255',
            'mymessage' => 'nullable|string|max:255',
            'SelectionMail' => 'nullable|string|max:255',
            'Messagesubject' => 'nullable|string|max:255',
            'Messagebody' => 'nullable|string|max:255',
        ]);

        $item->update([
            'liste2' => $validated['liste2'] ?? null,
            'comptage' => $validated['comptage'] ?? null,
            'maxchar' => $validated['maxchar'] ?? null,
            'mode' => $validated['mode'] ?? null,
            'mymessage' => $validated['mymessage'] ?? null,
            'SelectionMail' => $validated['SelectionMail'] ?? null,
            'Messagesubject' => $validated['Messagesubject'] ?? null,
            'Messagebody' => $validated['Messagebody'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.mail_create.edit', $item->id)
            ->with('success', 'MailCreate updated successfully');
    }
                

    public function destroy($id)
    {
        $item = MailCreate::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.mail_create.index')
            ->with('success', 'MailCreate deleted successfully');
    }
                
}
