<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TutorialController extends Controller
{
    public function index()
    {
        return view('tutoriales.tutoriales');
    }

    public function show($slug)
    {
        // Obtener el tutorial específico
        $tutorial = DB::table('tutoriales')->where('slug', $slug)->first();
        
        if (!$tutorial) {
            abort(404, 'Tutorial no encontrado');
        }

        // Verificar si el usuario ya completó este tutorial
        $usuarioId = session('usuario_id');
        $progreso = DB::table('progreso_tutorial')
            ->where('usuario_id', $usuarioId)
            ->where('tutorial_id', $tutorial->id)
            ->first();

        // Cargar la vista específica del tutorial
        $vista = 'tutoriales.' . $slug;
        
        if (!view()->exists($vista)) {
            return view('tutoriales.en-desarrollo', compact('slug'));
        }

        return view($vista, compact('tutorial', 'progreso'));
    }

    public function completar(Request $request, $slug)
    {
        $usuarioId = session('usuario_id');
        
        if (!$usuarioId) {
            return response()->json([
                'success' => false,
                'error' => 'Usuario no autenticado'
            ]);
        }

        // Obtener el tutorial
        $tutorial = DB::table('tutoriales')->where('slug', $slug)->first();
        
        if (!$tutorial) {
            return response()->json([
                'success' => false,
                'error' => 'Tutorial no encontrado'
            ]);
        }

        // Verificar si ya completó el tutorial
        $progresoExistente = DB::table('progreso_tutorial')
            ->where('usuario_id', $usuarioId)
            ->where('tutorial_id', $tutorial->id)
            ->first();

        if ($progresoExistente && $progresoExistente->estado === 'completado') {
            return response()->json([
                'success' => false,
                'error' => 'Ya completaste este tutorial'
            ]);
        }

        DB::transaction(function () use ($usuarioId, $tutorial, $progresoExistente) {
            if ($progresoExistente) {
                // Actualizar estado existente
                DB::table('progreso_tutorial')
                    ->where('id', $progresoExistente->id)
                    ->update([
                        'estado' => 'completado',
                        'updated_at' => now()
                    ]);
            } else {
                // Crear nuevo registro
                DB::table('progreso_tutorial')->insert([
                    'usuario_id' => $usuarioId,
                    'tutorial_id' => $tutorial->id,
                    'estado' => 'completado',
                    'fecha' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Sumar puntos al usuario
            DB::table('usuarios')
                ->where('id', $usuarioId)
                ->increment('puntos_totales', $tutorial->puntos_otorgados);

            // Actualizar sesión
            $usuario = DB::table('usuarios')->where('id', $usuarioId)->first();
            $puntosDisponibles = ($usuario->puntos_totales ?? 0) - ($usuario->puntos_usados ?? 0);
            
            session([
                'puntos_disponibles' => $puntosDisponibles,
                'puntos_totales' => $usuario->puntos_totales ?? 0
            ]);
        });

        return response()->json([
            'success' => true,
            'puntos_ganados' => $tutorial->puntos_otorgados,
            'puntos_totales' => session('puntos_disponibles')
        ]);
    }

    public function getProgreso()
{
    $usuarioId = session('usuario_id');
    
    if (!$usuarioId) {
        return response()->json([
            'success' => false,
            'progreso' => 0,
            'tutorialesCompletados' => 0,
            'totalTutoriales' => 10
        ]);
    }

    // Obtener tutoriales completados
    $tutorialesCompletados = DB::table('progreso_tutorial')
        ->where('usuario_id', $usuarioId)
        ->where('estado', 'completado')
        ->count();

    $totalTutoriales = 10; // Total de tutoriales en el sistema
    $progresoPorcentaje = round(($tutorialesCompletados / $totalTutoriales) * 100);

    return response()->json([
        'success' => true,
        'progreso' => $progresoPorcentaje,
        'tutorialesCompletados' => $tutorialesCompletados,
        'totalTutoriales' => $totalTutoriales,
        'puntos' => session('puntos_disponibles') ?? 0
    ]);
}
}