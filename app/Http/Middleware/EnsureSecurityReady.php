<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureSecurityReady
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->attributes->get('usuario_actual');

        if (! $usuario instanceof Usuario || ! $usuario->requiereCorreoVerificado()
            || ! Schema::hasColumn('usuarios', 'email_verified_at')) {
            return $next($request);
        }

        if (! $usuario->email) {
            return $this->block($request, 'Debes registrar tu correo electrónico para continuar.', 'EMAIL_REQUIRED', 'security.email.form');
        }

        if (! $usuario->email_verified_at) {
            return $this->block($request, 'Debes verificar tu correo electrónico para continuar.', 'EMAIL_VERIFICATION_REQUIRED', 'security.email.pending');
        }

        if ($usuario->esMaster()) {
            $dobleFactor = $usuario->twoFactorAuthentication;

            if (! $dobleFactor || ! $dobleFactor->confirmed_at) {
                return $this->block($request, 'Debes configurar el segundo factor de seguridad.', 'TWO_FACTOR_SETUP_REQUIRED', 'security.2fa.setup');
            }

            if ((int) $request->session()->get('two_factor_verified_user_id') !== (int) $usuario->id) {
                return $this->block($request, 'Debes completar el segundo factor de seguridad.', 'TWO_FACTOR_REQUIRED', 'security.2fa.challenge');
            }
        }

        return $next($request);
    }

    private function block(Request $request, string $message, string $code, string $route): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message, 'code' => $code], 403);
        }

        return redirect()->route($route)->with('warning', $message);
    }
}
