<?php

namespace App\Services\Api;

use App\Services\GeneralSettingService;

/**
 * Activation and secrets of the /api/v1 endpoints, read from the legacy
 * `configuration` rows so an upgraded eBrigade install keeps its keys:
 *
 * - `webservice_key` (id 50): export token; empty = export API disabled.
 * - `import_api` (id 64) + `import_api_token` (id 66): import switch and token.
 */
class ApiSettings
{
    public function __construct(private GeneralSettingService $settings) {}

    /** Export token, or '' when the export API is disabled. */
    public function exportToken(): string
    {
        return $this->settings->get('webservice_key');
    }

    /** Import token, or '' when the import API is disabled or has no token. */
    public function importToken(): string
    {
        if (! $this->settings->bool('import_api')) {
            return '';
        }

        return $this->settings->get('import_api_token');
    }
}
