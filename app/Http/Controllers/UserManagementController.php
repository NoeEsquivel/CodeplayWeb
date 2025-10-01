<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;

class UserManagementController extends Controller
{
    // ... métodos existentes ...

    // API para crear usuario
public function storeApi(Request $request)
{
    try {
        $request->validate([
            'nombre' => 'required|string|unique:usuarios',
            'rol' => 'required|in:Alumno,Administrador,Master',
            'clave' => 'required|string'
        ]);

        // Si el usuario actual es Administrador, forzar rol Alumno
        $rol = $request->rol;
        if (session('usuario_rol') === 'Administrador') {
            $rol = 'Alumno'; // Los administradores solo pueden crear alumnos
        }

        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'clave' => Hash::make($request->clave),
            'rol' => $rol,
            'cambio_clave_requerido' => true, // FORZAR a true para nuevos usuarios
            'puntos_totales' => 0,
            'puntos_usados' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Usuario creado exitosamente',
            'usuario' => $usuario
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

    // API para resetear contraseña
    public function resetPasswordApi(Request $request, $id)
    {
        try {
            $request->validate([
                'nueva_clave' => 'required|string'
            ]);

            $usuario = Usuario::findOrFail($id);
            $usuario->clave = Hash::make($request->nueva_clave);
            $usuario->cambio_clave_requerido = true; // IMPORTANTE: Marcar que requiere cambio
            $usuario->save();

            return response()->json([
                'success' => true,
                'message' => 'Contraseña reseteada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // API para eliminar usuario
    public function destroyApi($id)
    {
        try {
            // Prevenir que el Master se elimine a sí mismo
            if ($id == session('usuario_id')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes eliminar tu propio usuario'
                ], 400);
            }

            $usuario = Usuario::findOrFail($id);
            $usuario->delete();

            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}