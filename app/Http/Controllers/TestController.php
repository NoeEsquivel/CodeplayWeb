<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class TestController extends Controller
{
    public function show($modulo)
    {
        // Verificar si ya hizo el test hoy
        $usuarioId = session('usuario_id');
        $hoy = Carbon::today();
        
        $testHoy = DB::table('resultados_test')
            ->where('usuario_id', $usuarioId)
            ->whereDate('created_at', $hoy)
            ->where('test_id', function($query) use ($modulo) {
                $query->select('id')
                      ->from('tests')
                      ->where('modulo', $modulo);
            })
            ->exists();

        if ($testHoy) {
            return redirect()->route('alumno.documentacion')
                ->with('error', 'Ya realizaste este test hoy. Puedes intentarlo nuevamente mañana.');
        }

        // Obtener 5 preguntas aleatorias del módulo
        $test = DB::table('tests')
            ->where('modulo', $modulo)
            ->first();

        if (!$test) {
            abort(404, 'Test no encontrado');
        }

        $preguntas = DB::table('preguntas_test')
            ->where('test_id', $test->id)
            ->inRandomOrder()
            ->limit(5)
            ->get();

        return view('test.html1', compact('preguntas', 'modulo', 'test'));
    }

    public function evaluar(Request $request, $modulo)
    {
        $usuarioId = session('usuario_id');
        $hoy = Carbon::today();
        
        // Verificar nuevamente si ya hizo el test hoy
        $testHoy = DB::table('resultados_test')
            ->where('usuario_id', $usuarioId)
            ->whereDate('created_at', $hoy)
            ->where('test_id', function($query) use ($modulo) {
                $query->select('id')
                      ->from('tests')
                      ->where('modulo', $modulo);
            })
            ->exists();

        if ($testHoy) {
            return redirect()->route('alumno.documentacion')
                ->with('error', 'Ya realizaste este test hoy.');
        }

        $test = DB::table('tests')
            ->where('modulo', $modulo)
            ->first();

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

    public function checkAvailability($modulo)
{
    $usuarioId = session('usuario_id');
    $hoy = Carbon::today();
    
    $ultimoTest = DB::table('resultados_test')
        ->where('usuario_id', $usuarioId)
        ->where('test_id', function($query) use ($modulo) {
            $query->select('id')
                  ->from('tests')
                  ->where('modulo', $modulo);
        })
        ->orderBy('created_at', 'desc')
        ->first();

    if ($ultimoTest) {
        $proximoDisponible = Carbon::parse($ultimoTest->created_at)->addHours(24);
        
        if (Carbon::now()->lt($proximoDisponible)) {
            return response()->json([
                'canTakeTest' => false,
                'nextAvailableTime' => $proximoDisponible->toISOString()
            ]);
        }
    }

    return response()->json([
        'canTakeTest' => true,
        'nextAvailableTime' => null
    ]);
}



}