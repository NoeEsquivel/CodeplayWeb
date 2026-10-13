<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class CustomAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuarioId = $request->session()->get('usuario_id');

        if (! $usuarioId || ! $usuario = Usuario::find($usuarioId)) {
            return $this->unauthenticated($request);
        }

        $controlaSesiones = Schema::hasColumn('usuarios', 'session_version');

        if ($controlaSesiones && (
            ! $request->session()->has('usuario_session_version')
            || (int) $request->session()->get('usuario_session_version') !== (int) $usuario->session_version
        )) {
            return $this->unauthenticated($request, 'Tu sesión fue cerrada por un cambio de seguridad. Inicia sesión nuevamente.');
        }

        $usuario->loadMissing('colegio');
        if ((Schema::hasColumn('usuarios', 'activo') && ! $usuario->activo)
            || ($usuario->colegio && ! $usuario->colegio->activo && ! $usuario->esMaster())) {
            return $this->unauthenticated($request, 'Esta cuenta fue desactivada. Comunícate con la administración.');
        }
        if (Schema::hasTable('two_factor_authentications')) {
            $usuario->loadMissing('twoFactorAuthentication');
        }

        $request->attributes->set('usuario_actual', $usuario);
        $request->session()->put([
            'usuario_nombre' => $usuario->nombre,
            'usuario_rol' => $usuario->rol,
            'usuario_colegio_id' => $usuario->colegio_id,
            'usuario_colegio_nombre' => $usuario->colegio?->nombre,
            'cambio_clave_requerido' => (bool) $usuario->cambio_clave_requerido,
        ]);

        if ($usuario->cambio_clave_requerido && ! $request->routeIs('cambio.clave.*', 'logout')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes cambiar tu contraseña temporal antes de continuar.',
                    'code' => 'PASSWORD_CHANGE_REQUIRED',
                ], 403);
            }

            return redirect()->route('cambio.clave.obligatorio');
        }

        return $next($request);
    }

    private function unauthenticated(Request $request, string $message = 'Debes iniciar sesión para continuar.'): Response
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], 401);
        }

        return redirect()->route('login')->with('error', $message);
    }
}
