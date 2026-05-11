<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Evenement;
use Illuminate\Http\Request;

/**
 * Legacy migration source: evenement_xls.php
 * Legacy pattern: export
 * Legacy permission id: 41
 * This file stems from a legacy migration and requires functional verification.
 */
class EvenementXlsController extends Controller
{
    public function index(Request $request)
    {
        $query = Evenement::query();
        if (session()->has('SES_SECTION')) {
            $query->where('section_id', session('SES_SECTION'));
        }

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('eh_id_eh_id', 'like', '%' . $term . '%');
                $query->orWhere('e_code', 'like', '%' . $term . '%');
                $query->orWhere('s_id', 'like', '%' . $term . '%');
                $query->orWhere('te_code', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.evenement_xls.index', [
            'items' => $items,
        ]);
    }
}
