<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupSetting extends Model
{
    protected $table = 'ob_backup_settings';

    /** Available `frequency` values, for validation and the settings form. */
    public const FREQUENCIES = ['hourly', 'daily', 'weekly', 'monthly'];

    /** ISO-8601-ish day-of-week labels (0=dimanche..6=samedi), for the settings form. */
    public const DAYS_OF_WEEK = [
        0 => 'Dimanche',
        1 => 'Lundi',
        2 => 'Mardi',
        3 => 'Mercredi',
        4 => 'Jeudi',
        5 => 'Vendredi',
        6 => 'Samedi',
    ];

    /**
     * Naming pattern presets offered in the settings form. Tokens are expanded
     * by BackupController::buildFilename: {date}=Y-m-d, {time}=H-i-s, {database}=DB name.
     */
    public const NAMING_PATTERNS = [
        'backup_{date}_{time}'    => 'backup_2026-06-08_03-00-00',
        '{database}_{date}_{time}' => 'openbrigade_2026-06-08_03-00-00',
        '{date}_{time}_backup'    => '2026-06-08_03-00-00_backup',
        '{database}-{date}'       => 'openbrigade-2026-06-08',
    ];

    protected $fillable = [
        'retention_count',
        'auto_enabled',
        'frequency',
        'run_time',
        'start_date',
        'day_of_week',
        'day_of_month',
        'naming_pattern',
        'last_auto_backup_at',
    ];

    protected $casts = [
        'auto_enabled'        => 'boolean',
        'start_date'          => 'date',
        'last_auto_backup_at' => 'datetime',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }
}
