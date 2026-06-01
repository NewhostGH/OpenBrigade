<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Activity monitoring — recent actions in the audit log.
     */
    public function monitoring(Request $request): View
    {
        $search   = trim((string) $request->string('q'));
        $ltCode   = (string) $request->string('type', 'ALL');
        $pid      = (int) $request->integer('user', 0);

        $query = DB::table('log_history as lh')
            ->leftJoin('pompier as p', 'lh.P_ID', '=', 'p.P_ID')
            ->leftJoin('log_type as lt', 'lh.LT_CODE', '=', 'lt.LT_CODE')
            ->select(
                'lh.LH_ID', 'lh.LH_STAMP', 'lh.LH_COMPLEMENT', 'lh.LT_CODE',
                'lt.LT_DESCRIPTION',
                DB::raw("CONCAT(p.P_PRENOM, ' ', p.P_NOM) as actor")
            )
            ->orderByDesc('lh.LH_STAMP');

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('lh.LH_COMPLEMENT', 'like', "%{$search}%")
                  ->orWhere('p.P_NOM', 'like', "%{$search}%");
            });
        }

        if ($ltCode !== 'ALL') {
            $query->where('lh.LT_CODE', $ltCode);
        }

        if ($pid > 0) {
            $query->where('lh.P_ID', $pid);
        }

        $items    = $query->paginate(50)->withQueryString();
        $logTypes = DB::table('log_type')->orderBy('LT_DESCRIPTION')->get(['LT_CODE', 'LT_DESCRIPTION']);

        return view('admin.monitoring', compact('items', 'search', 'ltCode', 'logTypes'));
    }
}
