<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\ExportSql;
use Illuminate\Http\Request;

/**
 * Legacy migration source: export-sql.php
 * Legacy pattern: export
 * Legacy permission id: 27
 * This file stems from a legacy migration and requires functional verification.
 */
class ExportSqlController extends Controller
{
    public function index(Request $request)
    {
        $query = ExportSql::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('r_name', 'like', '%' . $term . '%');
                $query->orWhere('type', 'like', '%' . $term . '%');
                $query->orWhere('description', 'like', '%' . $term . '%');
                $query->orWhere('phpfromexportpompier', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.export_sql.index', [
            'items' => $items,
        ]);
    }
}
