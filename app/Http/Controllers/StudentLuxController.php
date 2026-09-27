<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentLuxController extends Controller
{
    public function welcomeSeen(Request $request): JsonResponse
    {
        $usuario = $request->attributes->get('usuario_actual');
        abort_unless($usuario instanceof Usuario && $usuario->esAlumno(), 403);

        $claimed = DB::table('usuarios')
            ->where('id', $usuario->id)
            ->where('rol', Usuario::ROL_ALUMNO)
            ->whereNull('lux_bienvenida_vista_at')
            ->update(['lux_bienvenida_vista_at' => now()]);

        return response()->json(['ok' => true, 'show' => $claimed === 1]);
    }
}
