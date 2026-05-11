<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\IndispoList;
use Illuminate\Http\Request;

/**
 * Legacy migration source: indispo_list_xls.php
 * Legacy pattern: export
 * Legacy permission id: 56
 * This file stems from a legacy migration and requires functional verification.
 */
class IndispoListXlsController extends Controller
{
    public function index(Request $request)
    {
        $query = IndispoList::query();
        if (session()->has('SES_COMPANY')) {
            $query->where('company_id', session('SES_COMPANY'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('i_code', 'like', '%' . $term . '%');
                $query->orWhere('p_id', 'like', '%' . $term . '%');
                $query->orWhere('p_nom', 'like', '%' . $term . '%');
                $query->orWhere('p_prenom', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.indispo_list_xls.index', [
            'items' => $items,
        ]);
    }
}
