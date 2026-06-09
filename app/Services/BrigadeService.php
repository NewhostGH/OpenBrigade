<?php

namespace App\Services;

/**
 * Core brigade operations service.
 * Handles personnel, events, schedules, and organizational structure.
 */
class BrigadeService implements ServiceInterface
{
    /**
     * Create a new brigade service instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get brigade metadata (name, version, contact info).
     */
    public function getMetadata(): array
    {
        return [
            'name' => config('app.name'),
            'version' => config('brigade.version'),
            'environment' => config('app.env'),
        ];
    }
}
