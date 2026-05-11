<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\RepoEvents;
use Illuminate\Http\Request;

/**
 * Legacy migration source: repo_events.php
 * Legacy pattern: list
 * Legacy permission id: 27
 * This file stems from a legacy migration and requires functional verification.
 */
class RepoEventsController extends Controller
{
    public function index(Request $request)
    {
        $query = RepoEvents::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('value', 'like', '%' . $term . '%');
                $query->orWhere('echooptgrouplabelutilisationshow_option0', 'like', '%' . $term . '%');
                $query->orWhere('connexionsparsectionshow_option23', 'like', '%' . $term . '%');
                $query->orWhere('systmesdexploitationutilissshow_option24', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.repo_events.index', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('legacy_migrated.repo_events.form', [
            'item' => null,
        ]);
    }
                

    public function edit($id)
    {
        $item = RepoEvents::findOrFail($id);

        return view('legacy_migrated.repo_events.form', [
            'item' => $item,
        ]);
    }
                

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'dtdb' => 'nullable|string|max:255',
            'btGo' => 'nullable|string|max:255',
            'dtfn' => 'nullable|string|max:255',
            'report' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'equipe' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
        ]);

        $item = RepoEvents::create([
            'sub' => $validated['sub'] ?? null,
            'dtdb' => $validated['dtdb'] ?? null,
            'btGo' => $validated['btGo'] ?? null,
            'dtfn' => $validated['dtfn'] ?? null,
            'report' => $validated['report'] ?? null,
            'section' => $validated['section'] ?? null,
            'type' => $validated['type'] ?? null,
            'equipe' => $validated['equipe'] ?? null,
            'year' => $validated['year'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.repo_events.edit', $item->id)
            ->with('success', 'RepoEvents created successfully');
    }
                

    public function update(Request $request, $id)
    {
        $item = RepoEvents::findOrFail($id);

        $validated = $request->validate([
            'sub' => 'nullable|string|max:255',
            'dtdb' => 'nullable|string|max:255',
            'btGo' => 'nullable|string|max:255',
            'dtfn' => 'nullable|string|max:255',
            'report' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'equipe' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:255',
        ]);

        $item->update([
            'sub' => $validated['sub'] ?? null,
            'dtdb' => $validated['dtdb'] ?? null,
            'btGo' => $validated['btGo'] ?? null,
            'dtfn' => $validated['dtfn'] ?? null,
            'report' => $validated['report'] ?? null,
            'section' => $validated['section'] ?? null,
            'type' => $validated['type'] ?? null,
            'equipe' => $validated['equipe'] ?? null,
            'year' => $validated['year'] ?? null,
        ]);

        return redirect()->route('legacy_migrated.repo_events.edit', $item->id)
            ->with('success', 'RepoEvents updated successfully');
    }
                

    public function destroy($id)
    {
        $item = RepoEvents::findOrFail($id);
        $item->delete();

        return redirect()->route('legacy_migrated.repo_events.index')
            ->with('success', 'RepoEvents deleted successfully');
    }
                
}
