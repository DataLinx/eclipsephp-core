<?php

namespace Eclipse\Core\Http\Middleware;

use Closure;
use Eclipse\Core\Models\Site;
use Eclipse\Core\Services\Registry;
use Illuminate\Http\Request;
use Opcodes\LogViewer\Facades\LogViewer;
use Symfony\Component\HttpFoundation\Response;

class SetupSite
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $site = Site::where('domain', $request->getHost())->first();

        if (! $site) {
            abort(404);
        }

        Registry::setSite($site);

        // Set log viewer restriction... must be done after the site is initialized
        LogViewer::auth(function ($request) {
            return $request->user() && $request->user()->hasRole('super_admin');
        });

        return $next($request);
    }
}
