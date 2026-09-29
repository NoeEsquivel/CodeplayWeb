<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Services\SecurityMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    public function __construct(private readonly SecurityMailService $mail)
    {
    }

    public function verify(Request $request)
    {
        $datos = $request->validate(['nombre' => ['required', 'string', 'max:100'], 'clave' => ['required', 'string']]);
        $nombre = preg_replace('/\s+/u', ' ', trim((string) $datos['nombre'])) ?? trim((string) $datos['nombre']);
        $usuario = Usuario::query()->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre, 'UTF-8')])->first();

        if (!$usuario || !Hash::check($datos['clave'], $usuario->clave)) {
            return redirect()->route('login')->with('error', 'Nombre de usuario o contraseña incorrectos');
        }

        $usuario->load('colegio');
        if ((Schema::hasColumn('usuarios', 'activo') && !$usuario->activo)
            || ($usuario->colegio && !$usuario->colegio->activo && !$usuario->esMaster())) {
            return redirect()->route('login')
                ->with('error', 'Esta cuenta no se encuentra activa. Comunícate con la administración.');
        }

        if (Schema::hasTable('two_factor_authentications')) {
            $usuario->load('twoFactorAuthentication');
        }
        $usaClaveTemporal = Hash::check('Ined1234', $usuario->clave);
        $usuario->forceFill([
            'ultimo_acceso' => now(),
            'cambio_clave_requerido' => $usuario->cambio_clave_requerido || $usaClaveTemporal,
        ])->save();

        $request->session()->regenerate();
        $request->session()->forget('two_factor_verified_user_id');
        $request->session()->put([
            'usuario_id' => $usuario->id,
            'usuario_nombre' => $usuario->nombre,
            'usuario_rol' => $usuario->rol,
            'usuario_colegio_id' => $usuario->colegio_id,
            'usuario_colegio_nombre' => $usuario->colegio?->nombre,
            'usuario_session_version' => (int) $usuario->session_version,
            'cambio_clave_requerido' => (bool) $usuario->cambio_clave_requerido,
            'puntos_disponibles' => ($usuario->puntos_totales ?? 0) - ($usuario->puntos_usados ?? 0),
            'puntos_totales' => $usuario->puntos_totales ?? 0,
            'puntos_usados' => $usuario->puntos_usados ?? 0,
        ]);

        if ($usuario->cambio_clave_requerido) {
            return redirect()->route('cambio.clave.obligatorio');
        }

        return $this->redirectByRole($request, $usuario);
    }

    public function mostrarCambioClaveObligatorio() { return view('cambio-clave-obligatorio'); }

    public function mostrarActualizacionClave()
    {
        return view('cambio-clave-obligatorio', ['modoVoluntario' => true]);
    }

    public function procesarCambioClaveObligatorio(Request $request)
    {
        $request->validate([
            'clave_actual' => ['required', 'string'],
            'nueva_clave' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'nueva_clave.min' => 'La contraseña debe tener al menos 8 caracteres',
            'nueva_clave.confirmed' => 'Las contraseñas no coinciden',
        ]);

        $usuario = $request->attributes->get('usuario_actual');
        abort_unless($usuario instanceof Usuario, 401, 'Usuario no autenticado.');

        if (!Hash::check($request->clave_actual, $usuario->clave)) {
            return back()->with('error', 'La contraseña actual es incorrecta');
        }

        $errores = $this->validarRequisitosClave($request->nueva_clave);
        if ($errores !== []) {
            return back()->with('errores_clave', $errores);
        }
        if (Hash::check($request->nueva_clave, $usuario->clave)) {
            return back()->with('errores_clave', ['La nueva contraseña debe ser diferente de la contraseña actual.']);
        }

        $usuario->forceFill([
            'clave' => Hash::make($request->nueva_clave),
            'cambio_clave_requerido' => false,
            'session_version' => ((int) $usuario->session_version) + 1,
        ])->save();

        $request->session()->regenerate();
        $request->session()->put([
            'cambio_clave_requerido' => false,
            'usuario_session_version' => (int) $usuario->session_version,
        ]);

        if ($usuario->email_verified_at && $usuario->email) {
            $this->mail->send($usuario->email, 'Contraseña de CodePlay actualizada', 'La contraseña de tu cuenta fue modificada.');
        }

        return $this->redirectByRole($request, $usuario)
            ->with('success', 'Contraseña actualizada exitosamente. Ahora puedes acceder a tu panel.');
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    private function redirectByRole(Request $request, Usuario $usuario)
    {
        if (Schema::hasTable('two_factor_authentications')
            && $usuario->esMaster() && $usuario->email_verified_at && $usuario->twoFactorAuthentication?->confirmed_at
            && (int) $request->session()->get('two_factor_verified_user_id') !== (int) $usuario->id) {
            return redirect()->route('security.2fa.challenge');
        }

        return match ($usuario->rol) {
            Usuario::ROL_ALUMNO => redirect()->route('inicio.alumno'),
            Usuario::ROL_PROFESOR => redirect()->route('inicio.profesor'),
            Usuario::ROL_ADMINISTRADOR_COLEGIO => redirect()->route('inicio.administrador-colegio'),
            Usuario::ROL_MASTER => redirect()->route('inicio.master'),
            default => $this->cerrarSesionPorRolInvalido($request),
        };
    }

    private function cerrarSesionPorRolInvalido(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('error', 'Rol no válido');
    }

    private function validarRequisitosClave(string $clave): array
    {
        $errores = [];
        if (!preg_match('/[A-Z]/', $clave)) $errores[] = 'La contraseña debe contener al menos una letra mayúscula';
        if (!preg_match('/[0-9]/', $clave)) $errores[] = 'La contraseña debe contener al menos un número';
        return $errores;
    }
}
