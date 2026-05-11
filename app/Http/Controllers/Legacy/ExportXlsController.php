<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\ExportXls;
use Illuminate\Http\Request;

/**
 * Legacy migration source: export-xls.php
 * Legacy pattern: export
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class ExportXlsController extends Controller
{
    public function index(Request $request)
    {
        $query = ExportXls::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.export_xls.index', [
            'items' => $items,
        ]);
    }
}
