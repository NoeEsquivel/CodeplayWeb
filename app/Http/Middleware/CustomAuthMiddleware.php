<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session('usuario_id')) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para acceder a esta página');
        }

        return $next($request);
    }
}