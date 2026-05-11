<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Qualifications;
use Illuminate\Http\Request;

/**
 * Legacy migration source: qualifications_xls.php
 * Legacy pattern: export
 * Legacy permission id: 0
 * This file stems from a legacy migration and requires functional verification.
 */
class QualificationsXlsController extends Controller
{
    public function index(Request $request)
    {
        $query = Qualifications::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('type', 'like', '%' . $term . '%');
                $query->orWhere('eq_id', 'like', '%' . $term . '%');
                $query->orWhere('ps_id', 'like', '%' . $term . '%');
                $query->orWhere('description', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.qualifications_xls.index', [
            'items' => $items,
        ]);
    }
}
