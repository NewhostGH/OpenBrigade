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

    // ── Security settings ─────────────────────────────────────────────────────

    /** IDs that live on the dedicated security page, not on the generic settings page. */
    private const SECURITY_IDS = [15, 16, 17, 70, 48, 34, 36, 49];

    public function security(): View
    {
        $settings = DB::table('configuration')
            ->whereIn('ID', self::SECURITY_IDS)
            ->get()
            ->keyBy('ID');

        $charterUpdatedAt = DB::table('configuration')
            ->where('NAME', 'charte_updated_at')
            ->value('VALUE');

        return view('admin.security', compact('settings', 'charterUpdatedAt'));
    }

    // ── Application settings ──────────────────────────────────────────────────

    public function settings(): View
    {
        $rows = DB::table('configuration')
            ->where('HIDDEN', 0)
            ->whereNotIn('ID', DB::table('ob_feature')->whereNotNull('legacy_config_id')->pluck('legacy_config_id'))
            ->whereNotIn('ID', self::SECURITY_IDS)
            ->orderBy('TAB')
            ->orderBy('ORDERING')
            ->get();

        $tabs = [
            1 => ['label' => 'Général',          'icon' => 'sliders-h'],
            2 => ['label' => 'Options',          'icon' => 'sliders-h'],
            3 => ['label' => 'Sécurité',         'icon' => 'shield-alt'],
            4 => ['label' => 'Organisation',      'icon' => 'building'],
            5 => ['label' => 'Avancé',            'icon' => 'wrench'],
        ];

        $grouped = $rows->groupBy('TAB');
        $activeTab = (int) session('_settings_tab', 0);

        // Settings whose behaviour has changed or is not yet wired in Laravel.
        // ID => ['type' => 'obsolete'|'todo', 'note' => '...']
        $annotations = [
            88 => ['type' => 'obsolete', 'note' => 'Le logo dans la navbar ne sera plus utilisé. L\'icône de maison sera utilisée à la place. Ce réglage n\'a plus d\'effet.'],
            76 => ['type' => 'todo',     'note' => 'Le fuseau horaire n\'est pas encore utilisé dans Laravel.'],
            96 => ['type' => 'obsolete', 'note' => 'Le pays des victimes par défaut sera géré par la localisation de Laravel. Ce réglage n\'a plus d\'effet.'],
            97 => ['type' => 'obsolete', 'note' => 'Le pays par défaut pour la géolocalisation sera géré par la localisation de Laravel. Ce réglage n\'a plus d\'effet.'],
            98 => ['type' => 'todo',     'note' => 'La devise par défaut n\'est pas encore utilisée dans Laravel.'],
            99 => ['type' => 'todo',     'note' => 'La devise par défaut n\'est pas encore utilisée dans Laravel.'],
            100 => ['type' => 'todo',     'note' => 'Le préfixe des numéros n\'est pas encore utilisé dans Laravel.'],
            101 => ['type' => 'todo',     'note' => 'La longueur des numéros n\'est pas encore utilisée dans Laravel.'],
            102 => ['type' => 'obsolete', 'note' => 'Les nom de niveaux de hierarchie ne sont plus utilisés. Ce réglage n\'a plus d\'effet.'],
            103 => ['type' => 'obsolete', 'note' => 'Les nom de niveaux de hierarchie ne sont plus utilisés. Ce réglage n\'a plus d\'effet.'],
            104 => ['type' => 'obsolete', 'note' => 'Les nom de niveaux de hierarchie ne sont plus utilisés. Ce réglage n\'a plus d\'effet.'],
            105 => ['type' => 'obsolete', 'note' => 'Les nom de niveaux de hierarchie ne sont plus utilisés. Ce réglage n\'a plus d\'effet.'],
            106 => ['type' => 'obsolete', 'note' => 'Les nom de niveaux de hierarchie ne sont plus utilisés. Ce réglage n\'a plus d\'effet.'],
            107 => ['type' => 'obsolete', 'note' => 'Les nom de niveaux de hierarchie ne sont plus utilisés. Ce réglage n\'a plus d\'effet.'],
            25 => ['type' => 'todo',     'note' => 'L\'historique des actions n\'est pas encore implémenté dans Laravel.'],
            79 => ['type' => 'obsolete', 'note' => 'Le type d\'organisation n\'est plus utilisé. Ce réglage n\'a plus d\'effet.'],
            6 => ['type' => 'todo',     'note' => 'Le nom de l\'organisation n\'est pas encore utilisé dans Laravel.'],
            7 => ['type' => 'obsolete', 'note' => 'L\'URL du site est gérée par APP_URL dans .env. Ce réglage n\'a plus d\'effet.'],
            8 => ['type' => 'todo',     'note' => 'Le mail de contact n\'est pas encore utilisé dans Laravel.'],
            39 => ['type' => 'todo',     'note' => 'Le nom de l\'organsiation n\'est pas encore utilisé dans Laravel.'],
            38 => ['type' => 'obsolete', 'note' => 'Le nom de l\'application est géré par APP_NAME dans .env. Ce réglage n\'a plus d\'effet.'],
            40 => ['type' => 'todo',     'note' => 'La description de l\'organisation n\'est pas encore utilisée dans Laravel.'],
            71 => ['type' => 'todo',     'note' => 'Le logo de l\'organisation n\'est pas encore utilisé dans Laravel.'],
            74 => ['type' => 'obsolete', 'note' => 'Le logo IOS de l\'application sera géré automatiquement à partir du logo principal. Ce réglage n\'a plus d\'effet.'],
            73 => ['type' => 'obsolete', 'note' => 'Le favicon de l\'application est géré par le logo principal. Ce réglage n\'a plus d\'effet.'],
            75 => ['type' => 'todo',     'note' => 'L\'image de connexion n\'est pas encore utilisée dans Laravel.'],
            2 => ['type' => 'obsolete', 'note' => 'Les sections ne sont plus limitées. Ce réglage n\'a plus d\'effet.'],
            13 => ['type' => 'obsolete', 'note' => 'Les sauvegardes automatiques sont gérées dans l\'onglet Sauvegardes. Ce réglage n\'a plus d\'effet.'],
            14 => ['type' => 'todo',     'note' => 'L\'optimisation de la base de données n\'est pas encore implémentée dans Laravel.'],
            26 => ['type' => 'obsolete', 'note' => 'Les Cron Jobs sont gérés par Laravel Scheduler. Ce réglage n\'a plus d\'effet.'],
            28 => ['type' => 'todo',     'note' => 'Les notifications par email ne sont pas encore implémentées dans Laravel.'],
            55 => ['type' => 'obsolete', 'note' => 'Les flocons de neige ne seront pas réimplémentés. Ce réglage n\'a plus d\'effet.'],
            63 => ['type' => 'obsolete', 'note' => 'Les changements du personnel peuvent être bloqués par des permissions. Ce réglage n\'a plus d\'effet.'],
            68 => ['type' => 'todo',     'note' => 'Les photos de profil obligatoires ne sont pas encore implémentées dans Laravel.'],
            64 => ['type' => 'todo',     'note' => 'L\'API n\'est pas encore implémentée dans Laravel.'],
            65 => ['type' => 'todo',     'note' => 'L\'URL de l\'API n\'est pas encore implémentée dans Laravel.'],
            37 => ['type' => 'todo',     'note' => 'Le mode de maintenance n\'est pas encore implémenté dans Laravel.'],
            41 => ['type' => 'todo',     'note' => 'Le texte de maintenance n\'est pas encore implémenté dans Laravel.'],
            66 => ['type' => 'todo',     'note' => 'Le token d\'API n\'est pas encore implémenté dans Laravel.'],
            9 => ['type' => 'todo',     'note' => 'Les SMS ne sont pas encore implémentés dans Laravel.'],
            10 => ['type' => 'todo',     'note' => 'Les SMS ne sont pas encore implémentés dans Laravel.'],
            11 => ['type' => 'todo',     'note' => 'Les SMS ne sont pas encore implémentés dans Laravel.'],
            12 => ['type' => 'todo',     'note' => 'Les SMS ne sont pas encore implémentés dans Laravel.'],
            33 => ['type' => 'todo',     'note' => 'Les données sensibles ne sont pas encore gérées dans Laravel.'],
            42 => ['type' => 'todo',     'note' => 'Les ACLs des fichiers ne sont pas encore gérées dans Laravel.'],
            48 => ['type' => 'obsolete', 'note' => 'Géré via Administration > Sécurité.'],
            15 => ['type' => 'obsolete', 'note' => 'Géré via Administration > Sécurité.'],
            16 => ['type' => 'obsolete', 'note' => 'Géré via Administration > Sécurité.'],
            17 => ['type' => 'obsolete', 'note' => 'Géré via Administration > Sécurité.'],
            70 => ['type' => 'obsolete', 'note' => 'Géré via Administration > Sécurité.'],
            34 => ['type' => 'obsolete', 'note' => 'Géré via Administration > Sécurité.'],
            36 => ['type' => 'obsolete', 'note' => 'Géré via Administration > Sécurité.'],
            49 => ['type' => 'obsolete', 'note' => 'Géré via Administration > Sécurité.'],
            50 => ['type' => 'obsolete', 'note' => 'La clé du webservice n\'est plus utilisée. Ce réglage n\'a plus d\'effet.'],
            80 => ['type' => 'todo',     'note' => 'La télémétrie n\'est pas encore implémentée dans Laravel.'],
            20 => ['type' => 'obsolete', 'note' => 'L\'URL de la page d\'identification est désormais fixe. Ce réglage n\'a plus d\'effet.'],
            51 => ['type' => 'obsolete', 'note' => 'L\'URL de redirection après connexion est désormais fixe. Ce réglage n\'a plus d\'effet.'],
            43 => ['type' => 'obsolete', 'note' => 'L\'ordre des sections n\'est plus utilisé. Ce réglage n\'a plus d\'effet.'],
            21 => ['type' => 'obsolete', 'note' => 'Les répertoires de stockage sont configurés dans config/filesystems.php. Ce réglage n\'a plus d\'effet.'],
            44 => ['type' => 'obsolete', 'note' => 'Laravel utilise bcrypt automatiquement. Les anciens hachages MD5 sont migrés à la prochaine connexion. Ce réglage n\'a plus d\'effet.'],
            54 => ['type' => 'obsolete', 'note' => 'L\'affichage des erreurs est contrôlé par APP_DEBUG dans .env. Ce réglage n\'a plus d\'effet.'],
            69 => ['type' => 'todo',     'note' => 'Le message de première connexion référence specific_info.php qui n\'existe pas encore dans Laravel.'],
            67 => ['type' => 'obsolete', 'note' => 'Le verrou de crontab de mailing est géré par Laravel Queue. Ce réglage n\'a plus d\'effet.'],
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

        if ($request->input('_back') === 'security') {
            return redirect()->route('admin.security')->with('success', 'Paramètre mis à jour.');
        }

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
