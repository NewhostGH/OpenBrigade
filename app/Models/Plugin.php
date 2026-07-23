<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * An installed plugin (code under plugins/<slug>, see App\Services\Plugins).
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string $version
 * @property string $min_app_version
 * @property string $max_app_version
 * @property bool $enabled
 * @property Carbon|null $installed_at
 */
class Plugin extends Model
{
    protected $table = 'ob_plugin';

    protected $fillable = ['slug', 'name', 'version', 'min_app_version', 'max_app_version', 'enabled', 'installed_at'];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'installed_at' => 'datetime',
        ];
    }
}
