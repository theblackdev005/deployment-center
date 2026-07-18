<?php

use App\Http\Middleware\EnsureTwoFactorConfigured;
use App\Http\Middleware\InstallationMiddleware;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustHosts(
            at: fn () => is_file(storage_path('app/installed.lock')) ? config('security.trusted_hosts') : ['.*'],
            subdomains: false,
        );
        $middleware->prependToGroup('web', InstallationMiddleware::class);
        $middleware->appendToGroup('web', SecurityHeaders::class);
        $middleware->alias([
            'two-factor.configured' => EnsureTwoFactorConfigured::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontFlash([
            'ssh_password', 'api_token', 'password', 'password_confirmation', 'current_password',
            'code', 'recovery_code',
            'github_token',
        ]);

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
