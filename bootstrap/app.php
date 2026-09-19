<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'puede' => \App\Http\Middleware\EnsurePermiso::class,
            'superadmin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'tenant' => \App\Http\Middleware\EnsureTenant::class,
            'suscripcion' => \App\Http\Middleware\EnsureSuscripcionActiva::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
