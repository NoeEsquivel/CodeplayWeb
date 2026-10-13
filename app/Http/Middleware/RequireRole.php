<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireRole
{
    public function __construct(private readonly EnsureSecurityReady $security) {}

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->attributes->get('usuario_actual');

        if (! $usuario instanceof Usuario) {
            $usuario = Usuario::find($request->session()->get('usuario_id'));
        }

        if (! $usuario || ! in_array($usuario->rol, $roles, true)) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'No tienes permiso para realizar esta acción.'], 403);
            }
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $this->security->handle($request, $next);
    }
}
