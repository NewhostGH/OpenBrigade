<?php

namespace App\Services\Plugins;

use RuntimeException;

/**
 * Any refusal along the plugin pipeline (invalid manifest, bad archive,
 * checksum mismatch, version conflict…). The message is already translated —
 * controllers flash it directly, never a 500.
 */
class InvalidPluginException extends RuntimeException {}
