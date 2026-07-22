<?php

namespace App\Http\Controllers;

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
