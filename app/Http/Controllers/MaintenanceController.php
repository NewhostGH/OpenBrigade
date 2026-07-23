<?php

namespace App\Http\Controllers;

use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    public function index(): View
    {
        $phpVersion = PHP_VERSION;
        $laravelVersion = app()->version();
        $dbVersion = DB::selectOne('SELECT VERSION() as v')->v ?? '—';
        $appVersion = DB::table('configuration')->where('ID', 1)->value('VALUE') ?? '—';
        $env = app()->environment();
        $debugMode = config('app.debug') ? 'Activé' : 'Désactivé';

        $status = $this->migrationStatus();

        // Maintenance-related configuration rows (hidden from the settings
        // grid — this page is their home): mode, banner text, auto-optimize.
        $maintSettings = DB::table('configuration')
            ->whereIn('NAME', ['maintenance_mode', 'maintenance_text', 'auto_optimize'])
            ->get()
            ->keyBy('NAME');

        return view('admin.maintenance.index', compact(
            'phpVersion', 'laravelVersion', 'dbVersion', 'appVersion', 'env', 'debugMode', 'status', 'maintSettings'
        ));
    }

    /** Clear the application/config/route/view caches. */
    public function clearCaches(): RedirectResponse
    {
        foreach (['cache:clear', 'config:clear', 'route:clear', 'view:clear'] as $command) {
            Artisan::call($command);
        }

        Audit::action('maintenance.caches_cleared');

        return redirect()->route('admin.maintenance')
            ->with('success', __('admin.maintenance.caches_cleared'));
    }

    /** Run OPTIMIZE TABLE now (bypasses the auto_optimize gate). */
    public function optimizeDatabase(): RedirectResponse
    {
        $code = Artisan::call('ob:db:optimize', ['--force' => true]);
        $summary = trim(Artisan::output());

        return redirect()->route('admin.maintenance')->with(
            $code === 0 ? 'success' : 'error',
            $summary !== '' ? $summary : __('admin.maintenance.optimize_done'),
        );
    }

    /** Trim the observability log to its retention window now. */
    public function pruneLogs(): RedirectResponse
    {
        Artisan::call('ob:logs:prune');
        Audit::action('maintenance.logs_pruned');

        return redirect()->route('admin.maintenance')
            ->with('success', __('admin.maintenance.logs_pruned'));
    }

    private function migrationStatus(): array
    {
        $ran = DB::table('migrations')
            ->pluck('migration')
            ->flip();

        $files = collect(File::glob(database_path('migrations/*.php')))
            ->merge(File::glob(database_path('migrations/**/*.php')))
            ->map(fn ($f) => pathinfo($f, PATHINFO_FILENAME))
            ->sort()
            ->values();

        return $files->map(fn ($name) => [
            'ran' => $ran->has($name),
            'name' => $name,
        ])->toArray();
    }
}
