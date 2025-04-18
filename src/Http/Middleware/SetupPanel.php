<?php

namespace Eclipse\Core\Http\Middleware;

use Closure;
use Eclipse\Core\Models\Locale;
use Filament\Facades\Filament;
use Filament\SpatieLaravelTranslatablePlugin;
use Illuminate\Http\Request;
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
        $localeIds = Locale::getAvailableLocales()->pluck('id')->toArray();

        $panel = Filament::getPanel();

        if ($panel) {
            // Set locales for the Translatable plugin
            $panel
                ->plugin(
                    SpatieLaravelTranslatablePlugin::make()
                        ->defaultLocales($localeIds)
                );
        }

        return $next($request);
    }
}
