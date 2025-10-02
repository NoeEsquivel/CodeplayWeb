<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;

class AuthController extends Controller
{
    public function verify(Request $request)
    {
        // Validación
        $request->validate([
            'nombre' => 'required|string',
            'clave' => 'required|string'
        ]);

        // Buscar usuario por nombre
        $usuario = Usuario::where('nombre', $request->nombre)->first();

        // Verificar si el usuario existe y la contraseña es correcta
        if (!$usuario || !Hash::check($request->clave, $usuario->clave)) {
            return redirect()->route('login')->with('error', 'Nombre de usuario o contraseña incorrectos');
        }

        // ✅ **NUEVO: ACTUALIZAR ÚLTIMO ACCESO**
        $usuario->update([
            'ultimo_acceso' => now()
        ]);

        // Crear sesión
        session([
            'usuario_id' => $usuario->id,
            'usuario_nombre' => $usuario->nombre,
            'usuario_rol' => $usuario->rol,
            'cambio_clave_requerido' => $usuario->cambio_clave_requerido
        ]);

        // Si la contraseña es temporal y requiere cambio, redirigir al formulario
        // IMPORTANTE: Esto aplica para TODOS los roles (Alumno, Administrador, Master)
        if ($usuario->cambio_clave_requerido || Hash::check('Ined1234', $usuario->clave)) {
            return redirect()->route('cambio.clave.obligatorio');
        }

        // Redirigir según el rol
        return $this->redirectByRole($usuario->rol);
    }

    private function redirectByRole($rol)
    {
        switch ($rol) {
            case 'Master':
                return redirect()->route('inicio.master');
            case 'Administrador':
                return redirect()->route('inicio.administrador');
            case 'Alumno':
            default:
                return redirect()->route('inicio.alumno');
        }
    }

    // Nuevo método para cambiar contraseña obligatorio
    public function mostrarCambioClaveObligatorio()
    {
        return view('cambio-clave-obligatorio');
    }

    public function procesarCambioClaveObligatorio(Request $request)
    {
        $request->validate([
            'clave_actual' => 'required|string',
            'nueva_clave' => 'required|string|min:8|confirmed',
        ], [
            'nueva_clave.min' => 'La contraseña debe tener al menos 8 caracteres',
            'nueva_clave.confirmed' => 'Las contraseñas no coinciden'
        ]);

        // Obtener usuario de la sesión
        $usuario = Usuario::find(session('usuario_id'));

        // Verificar contraseña actual
        if (!Hash::check($request->clave_actual, $usuario->clave)) {
            return back()->with('error', 'La contraseña actual es incorrecta');
        }

        // Validar requisitos de la nueva contraseña
        $errores = $this->validarRequisitosClave($request->nueva_clave);
        if (!empty($errores)) {
            return back()->with('errores_clave', $errores);
        }

        // Actualizar contraseña
        $usuario->clave = Hash::make($request->nueva_clave);
        $usuario->cambio_clave_requerido = false;
        $usuario->save();

        // Actualizar sesión
        session(['cambio_clave_requerido' => false]);

        // REDIRIGIR SEGÚN EL ROL CORRECTO
        return $this->redirectByRole($usuario->rol)
            ->with('success', 'Contraseña actualizada exitosamente. Ahora puedes acceder a tu panel.');
    }

    private function validarRequisitosClave($clave)
    {
        $errores = [];

        if (strlen($clave) < 8) {
            $errores[] = 'La contraseña debe tener al menos 8 caracteres';
        }

        if (!preg_match('/[A-Z]/', $clave)) {
            $errores[] = 'La contraseña debe contener al menos una letra mayúscula';
        }

        if (!preg_match('/[0-9]/', $clave)) {
            $errores[] = 'La contraseña debe contener al menos un número';
        }

        return $errores;
    }

    public function logout(Request $request)
    {
        // Limpiar sesión
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}