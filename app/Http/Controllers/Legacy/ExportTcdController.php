<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\ExportTcd;
use Illuminate\Http\Request;

/**
 * Legacy migration source: export-tcd.php
 * Legacy pattern: export
 * Legacy permission id: 27
 * This file stems from a legacy migration and requires functional verification.
 */
class ExportTcdController extends Controller
{
    public function index(Request $request)
    {
        $query = ExportTcd::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('________out__rowxcode________out__', 'like', '%' . $term . '%');
                $query->orWhere('total_par_section', 'like', '%' . $term . '%');
                $query->orWhere('total_par_activit', 'like', '%' . $term . '%');
                $query->orWhere('sumalphanumericalpharows', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.export_tcd.index', [
            'items' => $items,
        ]);
    }
}
