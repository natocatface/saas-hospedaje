<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuscripcionActiva
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->rol === 'superadmin') {
            return $next($request);
        }

        $empresa = $user?->empresa;

        if ($empresa && ! $empresa->vigente()) {
            return redirect()->route('suscripcion.renovar');
        }

        return $next($request);
    }
}
