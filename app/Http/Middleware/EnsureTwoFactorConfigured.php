<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorConfigured
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('security.two_factor_required') && ! $request->user()?->hasTwoFactorAuthentication()) {
            return redirect()->route('profile.edit')
                ->with('warning', 'Activez la double authentification avant d’accéder à la plateforme.');
        }

        return $next($request);
    }
}
