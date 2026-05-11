<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\ExportSqlListe;
use Illuminate\Http\Request;

/**
 * Legacy migration source: export-sql-liste.php
 * Legacy pattern: export
 * Legacy permission id: 27
 * This file stems from a legacy migration and requires functional verification.
 */
class ExportSqlListeController extends Controller
{
    public function index(Request $request)
    {
        $query = ExportSqlListe::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('count', 'like', '%' . $term . '%');
                $query->orWhere('count1', 'like', '%' . $term . '%');
                $query->orWhere('r_code', 'like', '%' . $term . '%');
                $query->orWhere('r_name', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.export_sql_liste.index', [
            'items' => $items,
        ]);
    }
}
