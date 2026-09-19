<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermiso
{
    public function handle(Request $request, Closure $next, string $permiso): Response
    {
        if (! $request->user() || ! $request->user()->puede($permiso)) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}
