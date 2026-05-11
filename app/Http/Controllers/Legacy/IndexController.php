<?php

    namespace App\Http\Controllers\Legacy;

    use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/**
 * Legacy migration source: index.php
 * Legacy pattern: dashboard
 * Legacy permission id: none
 * This file stems from a legacy migration and requires functional verification.
 */
class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $legacyHomeRoute = Route::has('legacy_migrated.personnel.index')
            ? 'legacy_migrated.personnel.index'
            : (Route::has('legacy_migrated.evenement_detail.index')
                ? 'legacy_migrated.evenement_detail.index'
                : null);

        if ($request->filled('evenement')) {
            cookie()->queue('evenement', (int) $request->input('evenement'), 1);
        }
        if ($request->filled('absence')) {
            cookie()->queue('absence', (int) $request->input('absence'), 1);
        }
        if ($request->filled('note')) {
            cookie()->queue('note', (int) $request->input('note'), 1);
        }

        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        if (!empty($user->password_expires_at) && now()->greaterThanOrEqualTo($user->password_expires_at)) {
            if (Route::has('legacy_migrated.change_password.index')) {
                return redirect()->route('legacy_migrated.change_password.index');
            }
            if ($legacyHomeRoute) {
                return redirect()->route($legacyHomeRoute);
            }
            return view('legacy_migrated.index');
        }

        if ($request->filled('evenement')) {
            if (Route::has('legacy_migrated.evenement_display.index')) {
                return redirect()->route('legacy_migrated.evenement_display.index', [
                    'evenement' => (int) $request->input('evenement'),
                ]);
            }
            if ($legacyHomeRoute) {
                return redirect()->route($legacyHomeRoute);
            }
            return view('legacy_migrated.index');
        }
        if ($request->filled('absence')) {
            if (Route::has('legacy_migrated.indispo_display.index')) {
                return redirect()->route('legacy_migrated.indispo_display.index', [
                    'code' => (int) $request->input('absence'),
                ]);
            }
            if ($legacyHomeRoute) {
                return redirect()->route($legacyHomeRoute);
            }
            return view('legacy_migrated.index');
        }
        if ($request->filled('note')) {
            if (Route::has('legacy_migrated.note_frais_edit.index')) {
                return redirect()->route('legacy_migrated.note_frais_edit.index', [
                    'nfid' => (int) $request->input('note'),
                ]);
            }
            if ($legacyHomeRoute) {
                return redirect()->route($legacyHomeRoute);
            }
            return view('legacy_migrated.index');
        }

        if ($request->cookie('evenement')) {
            if (Route::has('legacy_migrated.evenement_display.index')) {
                return redirect()->route('legacy_migrated.evenement_display.index', [
                    'evenement' => (int) $request->cookie('evenement'),
                ]);
            }
            if ($legacyHomeRoute) {
                return redirect()->route($legacyHomeRoute);
            }
            return view('legacy_migrated.index');
        }
        if ($request->cookie('absence')) {
            if (Route::has('legacy_migrated.indispo_display.index')) {
                return redirect()->route('legacy_migrated.indispo_display.index', [
                    'code' => (int) $request->cookie('absence'),
                ]);
            }
            if ($legacyHomeRoute) {
                return redirect()->route($legacyHomeRoute);
            }
            return view('legacy_migrated.index');
        }
        if ($request->cookie('note')) {
            if (Route::has('legacy_migrated.note_frais_edit.index')) {
                return redirect()->route('legacy_migrated.note_frais_edit.index', [
                    'nfid' => (int) $request->cookie('note'),
                ]);
            }
            if ($legacyHomeRoute) {
                return redirect()->route($legacyHomeRoute);
            }
            return view('legacy_migrated.index');
        }

        if ($legacyHomeRoute) {
            return redirect()->route($legacyHomeRoute);
        }

        return view('legacy_migrated.index');
    }
}
