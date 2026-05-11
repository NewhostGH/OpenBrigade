<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\MailSend;
use Illuminate\Http\Request;

/**
 * Legacy migration source: mail_send.php
 * Legacy pattern: generic
 * Legacy permission id: 43
 * This file stems from a legacy migration and requires functional verification.
 */
class MailSendController extends Controller
{
    public function index(Request $request)
    {
        $query = MailSend::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.mail_send.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.mail_send.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = MailSend::findOrFail($id);

        return view('legacy_migrated.mail_send.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item = MailSend::create([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.mail_send.edit', $item->id)
            ->with('success', 'MailSend created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = MailSend::findOrFail($id);

        $validated = $request->validate([
            'id' => 'nullable|integer',
        ]);

        $item->update([
            'updated_at' => now(),
        ]);

        return redirect()->route('legacy_migrated.mail_send.edit', $item->id)
            ->with('success', 'MailSend updated successfully');
    }
                

    public function destroy($id)
    {
        $item = MailSend::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.mail_send.index')
            ->with('success', 'MailSend deleted successfully');
    }
                
}
