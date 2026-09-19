<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->rol === 'superadmin') {
            return redirect()->route('plataforma.dashboard');
        }

        if ($user && ! $user->empresa_id) {
            abort(403, 'Tu usuario no está asociado a ninguna empresa.');
        }

        return $next($request);
    }
}
