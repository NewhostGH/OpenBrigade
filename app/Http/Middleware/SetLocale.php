<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the active UI locale for the request.
 *
 * Resolution order:
 *   1. A `locale` value stored in the session (set by a future locale switcher).
 *   2. The application default (config('app.locale')).
 *
 * Only locales advertised in config('app.supported_locales') are honoured so an
 * unknown value cannot be forced through the session. Today the only supported
 * locale is French; adding a second locale is a matter of dropping a new
 * `lang/<code>` directory and listing the code in `supported_locales`.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = (array) config('app.supported_locales', [config('app.locale')]);
        $locale = $request->session()->get('locale');

        if (is_string($locale) && in_array($locale, $supported, true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
