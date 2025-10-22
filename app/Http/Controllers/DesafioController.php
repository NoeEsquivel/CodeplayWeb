<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DesafioController extends Controller
{
    public function index()
    {
        $usuarioId = session('usuario_id');
        $desafiosCompletados = 0;
        
        if ($usuarioId) {
            $desafiosCompletados = DB::table('desafio_recompensas')
                ->where('usuario_id', $usuarioId)
                ->where('completado_primera_vez', true)
                ->count();
        }
        
        return view('desafios.index', compact('desafiosCompletados'));
    }

    public function programacion()
    {
        $usuarioId = session('usuario_id');
        $desafiosCompletados = 0;
        $nivelesCompletados = [];
        
        if ($usuarioId) {
            // Obtener número de desafíos completados
            $desafiosCompletados = DB::table('desafio_recompensas')
                ->where('usuario_id', $usuarioId)
                ->where('completado_primera_vez', true)
                ->count();
            
            // Obtener qué niveles específicos están completados
            $nivelesCompletados = DB::table('desafio_recompensas')
                ->where('usuario_id', $usuarioId)
                ->where('completado_primera_vez', true)
                ->pluck('nivel_desafio')
                ->toArray();
        }
        
        return view('desafios.desafio_programacion', compact('desafiosCompletados', 'nivelesCompletados'));
    }

    public function nivel1()
    {
        $usuarioId = session('usuario_id');
        $infoRecompensa = $this->obtenerInfoRecompensa($usuarioId, 1);
        
        return view('desafios.fundamentos_web.desafiolvl1', compact('infoRecompensa'));
    }

    // Función para obtener información de recompensa
    private function obtenerInfoRecompensa($usuarioId, $nivel)
    {
        $recompensa = DB::table('desafio_recompensas')
            ->where('usuario_id', $usuarioId)
            ->where('nivel_desafio', $nivel)
            ->first();

        $puntosBase = $this->obtenerPuntosPorNivel($nivel);
        $completadoPrimeraVez = $recompensa ? $recompensa->completado_primera_vez : false;
        $puedeReclamar = true;
        $tiempoRestante = null;
        $puntosOtorgar = $puntosBase;

        if ($recompensa && $recompensa->ultima_recompensa) {
            $ultimaRecompensa = Carbon::parse($recompensa->ultima_recompensa);
            $proximaRecompensa = $ultimaRecompensa->addHours(24);
            
            if (Carbon::now()->lt($proximaRecompensa)) {
                $puedeReclamar = false;
                $tiempoRestante = $proximaRecompensa->diffForHumans();
                $puntosOtorgar = floor($puntosBase / 2);
            } else {
                $puntosOtorgar = floor($puntosBase / 2);
            }
        }

        if ($completadoPrimeraVez) {
            $puntosOtorgar = floor($puntosBase / 2);
        }

        return [
            'completado_primera_vez' => $completadoPrimeraVez,
            'puede_reclamar' => $puedeReclamar,
            'tiempo_restante' => $tiempoRestante,
            'puntos_base' => $puntosBase,
            'puntos_otorgar' => $puntosOtorgar,
            'ya_completado' => $recompensa ? true : false
        ];
    }

    // Función para obtener puntos por nivel
    private function obtenerPuntosPorNivel($nivel)
    {
        $puntos = [
            1 => 50, 2 => 50, 3 => 50, 4 => 50,    // Niveles 1-4: 50 puntos
            5 => 100, 6 => 100, 7 => 100, 8 => 100, 9 => 100, // Niveles 5-9: 100 puntos
            10 => 150,                                // Nivel 10: 150 puntos
            11 => 200, 12 => 200, 13 => 200, 14 => 200, // Niveles 11-14: 200 puntos
            15 => 300,                                // Nivel 15: 300 puntos
            16 => 400, 17 => 400, 18 => 400, 19 => 400, // Niveles 16-19: 400 puntos
            20 => 1000                                // Nivel 20: 1000 puntos
        ];

        return $puntos[$nivel] ?? 50;
    }

    // Función para procesar la recompensa
    public function procesarRecompensa(Request $request)
    {
        $usuarioId = session('usuario_id');
        $nivel = $request->input('nivel');
        
        if (!$usuarioId) {
            return response()->json(['success' => false, 'error' => 'Usuario no autenticado']);
        }

        try {
            DB::beginTransaction();

            $recompensaExistente = DB::table('desafio_recompensas')
                ->where('usuario_id', $usuarioId)
                ->where('nivel_desafio', $nivel)
                ->first();

            $puntosBase = $this->obtenerPuntosPorNivel($nivel);
            $puntosOtorgar = $puntosBase;
            $completadoPrimeraVez = false;

            if ($recompensaExistente) {
                // Ya completado antes
                $completadoPrimeraVez = $recompensaExistente->completado_primera_vez;
                
                // Verificar si puede reclamar recompensa diaria
                if ($recompensaExistente->ultima_recompensa) {
                    $ultimaRecompensa = Carbon::parse($recompensaExistente->ultima_recompensa);
                    $proximaRecompensa = $ultimaRecompensa->addHours(24);
                    
                    if (Carbon::now()->lt($proximaRecompensa)) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false, 
                            'error' => 'recompensa_cooldown',
                            'tiempo_restante' => $proximaRecompensa->diffForHumans()
                        ]);
                    }
                }
                
                $puntosOtorgar = floor($puntosBase / 2);
                
                // Actualizar registro existente
                DB::table('desafio_recompensas')
                    ->where('usuario_id', $usuarioId)
                    ->where('nivel_desafio', $nivel)
                    ->update([
                        'puntos_obtenidos' => DB::raw('puntos_obtenidos + ' . $puntosOtorgar),
                        'ultima_recompensa' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
            } else {
                // Primera vez completando
                $completadoPrimeraVez = true;
                $puntosOtorgar = $puntosBase;
                
                // Crear nuevo registro
                DB::table('desafio_recompensas')->insert([
                    'usuario_id' => $usuarioId,
                    'nivel_desafio' => $nivel,
                    'completado_primera_vez' => true,
                    'puntos_obtenidos' => $puntosOtorgar,
                    'ultima_recompensa' => Carbon::now(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }

            // Actualizar puntos del usuario
            DB::table('usuarios')
                ->where('id', $usuarioId)
                ->update([
                    'puntos_totales' => DB::raw('puntos_totales + ' . $puntosOtorgar),
                    'updated_at' => Carbon::now()
                ]);

            // Actualizar sesión
            $usuario = DB::table('usuarios')->where('id', $usuarioId)->first();
            session(['puntos_disponibles' => ($usuario->puntos_totales - $usuario->puntos_usados)]);

            DB::commit();

            return response()->json([
                'success' => true,
                'puntos_otorgados' => $puntosOtorgar,
                'completado_primera_vez' => $completadoPrimeraVez,
                'puntos_totales' => $usuario->puntos_totales,
                'puntos_disponibles' => ($usuario->puntos_totales - $usuario->puntos_usados)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => 'Error al procesar recompensa: ' . $e->getMessage()]);
        }
    }
}