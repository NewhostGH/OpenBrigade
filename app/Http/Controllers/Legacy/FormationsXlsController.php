<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Formations;
use Illuminate\Http\Request;

/**
 * Legacy migration source: formations_xls.php
 * Legacy pattern: export
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class FormationsXlsController extends Controller
{
    public function index(Request $request)
    {
        $query = Formations::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('ps_id', 'like', '%' . $term . '%');
                $query->orWhere('type', 'like', '%' . $term . '%');
                $query->orWhere('pf_id', 'like', '%' . $term . '%');
                $query->orWhere('pf_comment', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.formations_xls.index', [
            'items' => $items,
        ]);
    }
}
