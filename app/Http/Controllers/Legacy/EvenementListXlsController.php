<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\EvenementList;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_list_xls.php
 * Legacy pattern: export
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementListXlsController extends Controller
{
    public function index(Request $request)
    {
        $query = EvenementList::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }
        if (session()->has('SES_COMPANY')) {
            $query->where('company_id', session('SES_COMPANY'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('tb_num', 'like', '%' . $term . '%');
                $query->orWhere('tb_libelle', 'like', '%' . $term . '%');
                $query->orWhere('te_code', 'like', '%' . $term . '%');
                $query->orWhere('te_libelle', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.evenement_list_xls.index', [
            'items' => $items,
        ]);
    }
}
