<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

/**
 * Legacy migration source: company_xls.php
 * Legacy pattern: export
 * Legacy permission id: 29
 * This file stems from a legacy migration and requires functional verification.
 */
class CompanyXlsController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('c_id', 'like', '%' . $term . '%');
                $query->orWhere('tc_code', 'like', '%' . $term . '%');
                $query->orWhere('c_name', 'like', '%' . $term . '%');
                $query->orWhere('s_id', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.company_xls.index', [
            'items' => $items,
        ]);
    }
}
