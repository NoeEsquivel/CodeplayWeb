<?php

namespace App\Http\Controllers;

use App\Services\TestAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class TestController extends Controller
{
    public function show(string $modulo, TestAvailabilityService $availability)
    {
        $usuarioId = session('usuario_id');
        $test = $availability->testForModule($modulo);
        $status = $availability->forTest((int) $usuarioId, (int) $test->id);

        if (!$status['disponible']) {
            return redirect()->route('alumno.documentacion')
                ->with('error', 'Este test todavía está en espera. Consulta Mi Progreso para ver el tiempo restante.');
        }

        // Obtener 5 preguntas aleatorias del módulo
        $preguntas = DB::table('preguntas_test')
            ->where('test_id', $test->id)
            ->inRandomOrder()
            ->limit(5)
            ->get();

        return view('test.html1', compact('preguntas', 'modulo', 'test'));
    }

    public function evaluar(Request $request, string $modulo, TestAvailabilityService $availability)
    {
        $usuarioId = session('usuario_id');
        $test = $availability->testForModule($modulo);
        $status = $availability->forTest((int) $usuarioId, (int) $test->id);

        if (!$status['disponible']) {
            return redirect()->route('alumno.documentacion')
                ->with('error', 'Este test todavía está en espera. Consulta Mi Progreso para ver el tiempo restante.');
        }

        $preguntas = DB::table('preguntas_test')
            ->where('test_id', $test->id)
            ->whereIn('id', array_keys($request->respuestas ?? []))
            ->get()
            ->keyBy('id');

        $puntaje = 0;
        $respuestasUsuario = [];
        $resultados = [];

        foreach ($request->respuestas as $preguntaId => $respuestaUsuario) {
            $pregunta = $preguntas[$preguntaId] ?? null;
            
            if ($pregunta) {
                $esCorrecta = $respuestaUsuario === $pregunta->respuesta_correcta;
                
                $resultados[] = [
                    'pregunta' => $pregunta->pregunta,
                    'respuesta_usuario' => $respuestaUsuario,
                    'respuesta_correcta' => $pregunta->respuesta_correcta,
                    'opciones' => [
                        'a' => $pregunta->opcion_a,
                        'b' => $pregunta->opcion_b,
                        'c' => $pregunta->opcion_c,
                        'd' => $pregunta->opcion_d,
                    ],
                    'es_correcta' => $esCorrecta
                ];

                if ($esCorrecta) {
                    $puntaje += 10;
                }
            }
        }

        // Guardar resultado en la base de datos
        DB::table('resultados_test')->insert([
            'usuario_id' => $usuarioId,
            'test_id' => $test->id,
            'puntaje' => $puntaje,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Actualizar puntos totales del usuario
        DB::table('usuarios')
            ->where('id', $usuarioId)
            ->increment('puntos_totales', $puntaje);

        return view('test.resultado', compact('puntaje', 'resultados', 'modulo'));
    }

    public function checkAvailability(string $modulo, TestAvailabilityService $availability)
    {
        $test = $availability->testForModule($modulo);
        $status = $availability->forTest((int) session('usuario_id'), (int) $test->id);

        return response()->json([
            'canTakeTest' => $status['disponible'],
            'nextAvailableTime' => $status['disponible'] ? null : $status['disponible_desde']?->toISOString(),
        ]);
    }



}
