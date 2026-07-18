<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InstallationMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('testing')) {
            return $next($request);
        }

        $installed = is_file(storage_path('app/installed.lock'));
        $installerRequest = $request->routeIs('installation.*');

        if ($installed && $installerRequest) {
            abort(404);
        }

        if (! $installed && ! $installerRequest) {
            return redirect()->route('installation.show');
        }

        return $next($request);
    }
}
