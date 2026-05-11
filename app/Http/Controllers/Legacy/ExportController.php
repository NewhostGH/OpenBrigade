<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Export;
use Illuminate\Http\Request;

/**
 * Legacy migration source: export.php
 * Legacy pattern: export
 * Legacy permission id: 27
 * This file stems from a legacy migration and requires functional verification.
 */
class ExportController extends Controller
{
    public function index(Request $request)
    {
        $query = Export::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('select', 'like', '%' . $term . '%');
                $query->orWhere('value', 'like', '%' . $term . '%');
                $query->orWhere('display_children21', 'like', '%' . $term . '%');
                $query->orWhere('0', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.export.index', [
            'items' => $items,
        ]);
    }
}
