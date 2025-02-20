<?php

namespace Eclipse\Core\Http\Middleware;

use Closure;
use Eclipse\Core\Models\Locale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class SetupPanel
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Set available languages for the Translatable package
        Config::set('translatable.locales', Locale::getAvailableLocales()->pluck('id')->toArray());

        // TODO Set tenant based on the HTTP host
        setPermissionsTeamId(1);

        return $next($request);
    }
}
