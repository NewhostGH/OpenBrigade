<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A plugin registry — a URL serving a registry.json catalog (KASM-style:
 * admins can add third-party registries next to the official one).
 *
 * @property int $id
 * @property string $name
 * @property string $url
 * @property bool $enabled
 * @property bool $is_default
 * @property bool $verify_ssl
 * @property bool $verify_checksum
 */
class PluginRegistry extends Model
{
    protected $table = 'ob_plugin_registry';

    protected $fillable = ['name', 'url', 'enabled', 'is_default', 'verify_ssl', 'verify_checksum'];

    protected $attributes = [
        'enabled' => true,
        'is_default' => false,
        'verify_ssl' => true,
        'verify_checksum' => true,
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'is_default' => 'boolean',
            'verify_ssl' => 'boolean',
            'verify_checksum' => 'boolean',
        ];
    }
}
