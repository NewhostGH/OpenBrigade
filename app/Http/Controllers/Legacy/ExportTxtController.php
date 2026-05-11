<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\ExportTxt;
use Illuminate\Http\Request;

/**
 * Legacy migration source: export-txt.php
 * Legacy pattern: export
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class ExportTxtController extends Controller
{
    public function index(Request $request)
    {
        $query = ExportTxt::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.export_txt.index', [
            'items' => $items,
        ]);
    }
}
