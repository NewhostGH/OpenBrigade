<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\ExportHtml;
use Illuminate\Http\Request;

/**
 * Legacy migration source: export-html.php
 * Legacy pattern: export
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class ExportHtmlController extends Controller
{
    public function index(Request $request)
    {
        $query = ExportHtml::query();

        if ($request->filled('query')) {
            $term = trim((string) $request->input('query'));
            $query->where(function ($query) use ($term) {
                $query->where('________________________________________ifin_arraycolcolsomme________________________out___number_formattotalsommecol0_________________________totalsommecol_0________________________________________else________________________out___nbsp________________________________________out___', 'like', '%' . $term . '%');
                $query->orWhere('________________________________________________ifin_arraycolcolsomme____________________out___number_format_float_totalsommecol0_________________________________else____________________out___nbsp________________________________out___', 'like', '%' . $term . '%');
                $query->orWhere('________________________________ifin_arraycolcolsomme____________________out___number_formatfloat_totalsommeglobalcol0_________________________________else____________________out___nbsp________________________________out___', 'like', '%' . $term . '%');
            });
        }

        $items = $query->paginate(20);

        return view('legacy_migrated.export_html.index', [
            'items' => $items,
        ]);
    }
}
