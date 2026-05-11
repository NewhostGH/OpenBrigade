<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Bilans;
use Illuminate\Http\Request;

/**
 * Legacy migration source: pdf_bilans.php
 * Legacy pattern: pdf
 * Legacy permission id: 27
 * This file stems from a legacy migration and requires functional verification.
 */
class PdfBilansController extends Controller
{
    public function index(Request $request)
    {
        $query = Bilans::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('s_id', 'like', '%' . $term . '%');
                $query->orWhere('s_code', 'like', '%' . $term . '%');
                $query->orWhere('s_description', 'like', '%' . $term . '%');
                $query->orWhere('s_parent', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.pdf_bilans.index', [
            'items' => $items,
        ]);
    }
}
