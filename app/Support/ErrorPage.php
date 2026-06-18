<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

/**
 * Metadata + layout resolution for the custom error pages.
 *
 * @see config/error_pages.php
 * @see resources/views/errors
 */
class ErrorPage
{
    /**
     * Title / message / diagram-node metadata for a status code.
     *
     * @return array{title:string,message:string,node:string,relogin?:bool}
     */
    public static function meta(int $code): array
    {
        return config("error_pages.codes.$code", config('error_pages.default'));
    }

    /**
     * Which Blade layout the error view should extend.
     *
     * The normal app shell stays visible for 4xx errors so the user keeps
     * their navigation — except when they must authenticate again (401, 419)
     * or are not logged in. Server-side failures (5xx) render standalone
     * because the shell itself may depend on the broken backend.
     */
    public static function layout(int $code): string
    {
        $meta = self::meta($code);
        $mustRelogin = $meta['relogin'] ?? false;

        if (Auth::check() && $code < 500 && ! $mustRelogin) {
            return 'layout.app';
        }

        return 'errors.standalone';
    }
}
