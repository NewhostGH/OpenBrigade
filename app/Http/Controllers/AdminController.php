<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function monitoring(Request $request): View
    {
        $search = trim((string) $request->string('q'));
        $ltCode = (string) $request->string('type', 'ALL');
        $pid = (int) $request->integer('user', 0);

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

        $items = $query->paginate(50)->withQueryString();
        $logTypes = DB::table('log_type')->orderBy('LT_DESCRIPTION')->get(['LT_CODE', 'LT_DESCRIPTION']);

        return view('admin.monitoring', compact('items', 'search', 'ltCode', 'logTypes')
            + ['columns' => $this->monitoringColumns()]);
    }

    // ── Application settings ──────────────────────────────────────────────────

    public function settings(): View
    {
        $rows = DB::table('configuration')
            ->where('HIDDEN', 0)
            ->orderBy('TAB')
            ->orderBy('ORDERING')
            ->get();

        $tabs = [
            1 => ['label' => 'Fonctionnalités', 'icon' => 'toggle-on'],
            2 => ['label' => 'Options',          'icon' => 'sliders-h'],
            3 => ['label' => 'Sécurité',         'icon' => 'shield-alt'],
            4 => ['label' => 'Organisation',      'icon' => 'building'],
            5 => ['label' => 'Avancé',            'icon' => 'wrench'],
            6 => ['label' => 'Modules',           'icon' => 'puzzle-piece'],
        ];

        $grouped = $rows->groupBy('TAB');
        $activeTab = (int) session('_settings_tab', 0);

        // Settings whose behaviour has changed or is not yet wired in Laravel.
        // ID => ['type' => 'obsolete'|'todo', 'note' => '...']
        $annotations = [
            44 => ['type' => 'obsolete', 'note' => 'Laravel utilise bcrypt automatiquement. Les anciens hachages MD5 sont migrés à la prochaine connexion. Ce réglage n\'a plus d\'effet.'],
            54 => ['type' => 'obsolete', 'note' => 'L\'affichage des erreurs est contrôlé par APP_DEBUG dans .env. Ce réglage n\'a plus d\'effet.'],
            15 => ['type' => 'todo',     'note' => 'La validation de la complexité du mot de passe n\'est pas encore implémentée dans Laravel.'],
            69 => ['type' => 'todo',     'note' => 'Le message de première connexion référence specific_info.php qui n\'existe pas encore dans Laravel.'],
            67 => ['type' => 'obsolete', 'note' => 'Le verrou de crontab de mailing est géré par Laravel Queue. Ce réglage n\'a plus d\'effet.'],
            70 => ['type' => 'todo',     'note' => 'L\'expiration des mots de passe n\'est pas encore implémentée dans Laravel.'],
        ];

        return view('admin.settings', compact('grouped', 'tabs', 'activeTab', 'annotations'));
    }

    public function saveSetting(Request $request, int $id): RedirectResponse
    {
        $row = DB::table('configuration')->where('ID', $id)->first();
        abort_if($row === null, 404);

        $tab = (int) $request->input('_tab', $row->TAB ?? 0);

        $value = $request->boolean('toggle')
            ? ($request->input('VALUE', '0') === '1' ? '1' : '0')
            : $request->input('VALUE', '');

        DB::table('configuration')
            ->where('ID', $id)
            ->update(['VALUE' => $value]);

        return redirect()->route('admin.settings')
            ->with('success', 'Paramètre mis à jour.')
            ->with('_settings_tab', $tab);
    }

    public function uploadSetting(Request $request, int $id): RedirectResponse
    {
        $row = DB::table('configuration')->where('ID', $id)->where('IS_FILE', 1)->first();
        abort_if($row === null, 404);

        $tab = (int) $request->input('_tab', $row->TAB ?? 0);

        $request->validate([
            'file' => ['required', 'file', 'mimes:jpeg,png,gif,ico,webp', 'max:4096'],
        ]);

        // Remove old file from storage
        $old = $row->VALUE ?? '';
        if ($old && str_starts_with($old, 'theme/') && Storage::disk('public')->exists($old)) {
            Storage::disk('public')->delete($old);
        }

        $path = $request->file('file')->store('theme', 'public');

        DB::table('configuration')->where('ID', $id)->update(['VALUE' => $path]);

        return redirect()->route('admin.settings')
            ->with('success', 'Image mise à jour.')
            ->with('_settings_tab', $tab);
    }

    public function deleteSetting(Request $request, int $id): RedirectResponse
    {
        $row = DB::table('configuration')->where('ID', $id)->where('IS_FILE', 1)->first();
        abort_if($row === null, 404);

        $tab = (int) $request->input('_tab', $row->TAB ?? 0);

        $path = $row->VALUE ?? '';
        if ($path && str_starts_with($path, 'theme/') && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        DB::table('configuration')->where('ID', $id)->update(['VALUE' => '']);

        return redirect()->route('admin.settings')
            ->with('success', 'Image supprimée.')
            ->with('_settings_tab', $tab);
    }

    private function monitoringColumns(): array
    {
        return [
            ['key' => 'date', 'label' => 'Date', 'type' => 'html', 'value' => fn ($log) => $log->LH_STAMP ? '<span style="white-space:nowrap">'.e(Carbon::parse($log->LH_STAMP)->format('d/m/Y H:i')).'</span>' : '—', 'alwaysVisible' => true, 'mobile' => true, 'exportable' => true, 'exportValue' => fn ($log) => $log->LH_STAMP ? Carbon::parse($log->LH_STAMP)->format('d/m/Y H:i') : ''],
            ['key' => 'utilisateur', 'label' => 'Utilisateur', 'type' => 'text', 'value' => fn ($log) => $log->actor ?? '—', 'alwaysVisible' => true, 'mobile' => true, 'exportable' => true, 'exportValue' => fn ($log) => $log->actor ?? ''],
            ['key' => 'action', 'label' => 'Action', 'type' => 'badge', 'value' => fn ($log) => $log->LT_CODE ?? 'OTHER', 'badgeMap' => ['LOGIN' => ['Connexion', 'ob-badge-int'], 'LOGOUT' => ['Déconnexion', 'ob-badge-archive'], 'UPDATE' => ['Modification', 'ob-badge-ben'], 'DELETE' => ['Suppression', 'ob-badge-bloqued'], 'OTHER' => ['Action', 'ob-badge-ext']], 'exportable' => true, 'exportValue' => fn ($log) => $log->LT_DESCRIPTION ?? $log->LT_CODE ?? '', 'mobile' => true],
            ['key' => 'detail', 'label' => 'Détail', 'type' => 'text', 'value' => fn ($log) => $log->LH_COMPLEMENT ?? '', 'mobile' => false, 'default' => false, 'exportable' => true, 'exportValue' => fn ($log) => $log->LH_COMPLEMENT ?? ''],
        ];
    }
}
