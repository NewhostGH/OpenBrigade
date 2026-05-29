<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(Request $request): View
    {
        $user      = auth()->user();
        $sectionId = (int) $user->P_SECTION;

        $search = trim((string) $request->string('q'));
        $type   = (string) $request->string('type', 'ALL');

        $query = DB::table('company as c')
            ->leftJoin('type_company as tc', 'c.TC_CODE', '=', 'tc.TC_CODE')
            ->where('c.S_ID', $sectionId)
            ->select(
                'c.C_ID', 'c.C_NAME', 'c.C_EMAIL', 'c.C_PHONE',
                'c.C_CITY', 'c.C_ZIP_CODE', 'c.TC_CODE',
                'tc.TC_LIBELLE'
            )
            ->orderBy('c.C_NAME');

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('c.C_NAME', 'like', "%{$search}%")
                  ->orWhere('c.C_CONTACT_NAME', 'like', "%{$search}%")
                  ->orWhere('c.C_EMAIL', 'like', "%{$search}%");
            });
        }

        if ($type !== 'ALL') {
            $query->where('c.TC_CODE', $type);
        }

        $items = $query->paginate(50)->withQueryString();

        $types = DB::table('type_company')->orderBy('TC_LIBELLE')->get(['TC_CODE', 'TC_LIBELLE']);

        return view('company.index', compact('items', 'search', 'type', 'types'));
    }
}
