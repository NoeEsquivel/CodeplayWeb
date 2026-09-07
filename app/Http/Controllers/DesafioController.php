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

    public function nivel2()
    {
        $usuarioId = session('usuario_id');
        $infoRecompensa = $this->obtenerInfoRecompensa($usuarioId, 2);
        
        return view('desafios.fundamentos_web.desafiolvl2', compact('infoRecompensa'));
    }

    public function nivel3()
    {
        $usuarioId = session('usuario_id');
        $infoRecompensa = $this->obtenerInfoRecompensa($usuarioId, 3);
        
        return view('desafios.fundamentos_web.desafiolvl3', compact('infoRecompensa'));
    }

    public function nivel4()
    {
        $usuarioId = session('usuario_id');
        $infoRecompensa = $this->obtenerInfoRecompensa($usuarioId, 4);
        
        return view('desafios.fundamentos_web.desafiolvl4', compact('infoRecompensa'));
    }

    public function nivel5()
    {
        $usuarioId = session('usuario_id');
        $infoRecompensa = $this->obtenerInfoRecompensa($usuarioId, 5);
        
        return view('desafios.fundamentos_web.desafiolvl5', compact('infoRecompensa'));
    }

    public function nivel6()
    {
        $usuarioId = session('usuario_id');
        $infoRecompensa = $this->obtenerInfoRecompensa($usuarioId, 6);
        
        return view('desafios.fundamentos_web.desafiolvl6', compact('infoRecompensa'));
    }

    public function nivel7()
    {
        $usuarioId = session('usuario_id');
        $infoRecompensa = $this->obtenerInfoRecompensa($usuarioId, 7);
        
        return view('desafios.fundamentos_web.desafiolvl7', compact('infoRecompensa'));
    }

    public function nivel8()
    {
        $usuarioId = session('usuario_id');
        $infoRecompensa = $this->obtenerInfoRecompensa($usuarioId, 8);
        
        return view('desafios.fundamentos_web.desafiolvl8', compact('infoRecompensa'));
    }

    public function nivel9()
    {
        $usuarioId = session('usuario_id');
        $infoRecompensa = $this->obtenerInfoRecompensa($usuarioId, 9);
        
        return view('desafios.fundamentos_web.desafiolvl9', compact('infoRecompensa'));
    }

    public function nivel10()
    {
        $usuarioId = session('usuario_id');
        $infoRecompensa = $this->obtenerInfoRecompensa($usuarioId, 10);
        
        return view('desafios.fundamentos_web.desafiolvl10', compact('infoRecompensa'));
    }

    public function nivel11()
    {
        $usuarioId = session('usuario_id');
        $infoRecompensa = $this->obtenerInfoRecompensa($usuarioId, 11);
        
        return view('desafios.fundamentos_web.desafiolvl11', compact('infoRecompensa'));
    }

    public function nivel12()
    {
        $usuarioId = session('usuario_id');
        $infoRecompensa = $this->obtenerInfoRecompensa($usuarioId, 12);
        
        return view('desafios.fundamentos_web.desafiolvl12', compact('infoRecompensa'));
    }

    public function nivel13()
    {
        $usuarioId = session('usuario_id');
        $infoRecompensa = $this->obtenerInfoRecompensa($usuarioId, 13);
        
        return view('desafios.fundamentos_web.desafiolvl13', compact('infoRecompensa'));
    }

    public function nivel14()
    {
        $usuarioId = session('usuario_id');
        $infoRecompensa = $this->obtenerInfoRecompensa($usuarioId, 14);
        
        return view('desafios.fundamentos_web.desafiolvl14', compact('infoRecompensa'));
    }

    public function nivel15()
    {
        $usuarioId = session('usuario_id');
        $infoRecompensa = $this->obtenerInfoRecompensa($usuarioId, 15);
        
        return view('desafios.fundamentos_web.desafiolvl15', compact('infoRecompensa'));
    }

    // Función para obtener información de recompensa
    public function nivel16()
    {
        $usuarioId = session('usuario_id');
        $infoRecompensa = $this->obtenerInfoRecompensa($usuarioId, 16);

        return view('desafios.fundamentos_web.desafiolvl16', compact('infoRecompensa'));
    }

    public function nivel17()
    {
        $usuarioId = session('usuario_id');
        $infoRecompensa = $this->obtenerInfoRecompensa($usuarioId, 17);

        return view('desafios.fundamentos_web.desafiolvl17', compact('infoRecompensa'));
    }

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
        $datos = $request->validate([
            'nivel' => ['required', 'integer', 'between:1,17'],
            'codigo' => ['required', 'string', 'max:60000'],
        ]);

        $nivel = (int) $datos['nivel'];
        $codigo = $datos['codigo'];
        
        if (!$usuarioId) {
            return response()->json(['success' => false, 'error' => 'Usuario no autenticado']);
        }

        if (!$this->codigoCumpleRequisitos($nivel, $codigo)) {
            return response()->json([
                'success' => false,
                'error' => 'El código no cumple los requisitos del desafío.',
                'message' => 'El código no cumple los requisitos del desafío.',
            ], 422);
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

    /**
     * Repite en el servidor los requisitos educativos de cada desafío.
     * El código se analiza como texto: nunca se evalúa ni se envía a SQL.
     */
    private function codigoCumpleRequisitos(int $nivel, string $codigo): bool
    {
        $lower = mb_strtolower($codigo, 'UTF-8');
        $compact = preg_replace('/\s+/', '', $lower) ?? '';

        $contieneTodos = static function (array $fragmentos) use ($lower): bool {
            foreach ($fragmentos as $fragmento) {
                if (!str_contains($lower, $fragmento)) {
                    return false;
                }
            }

            return true;
        };

        return match ($nivel) {
            1 => $contieneTodos(['<h1', '<p', '<strong', '<em']),
            2 => $contieneTodos(['<h1', '<h2', '<p', '<ul', '<li']),
            3 => $contieneTodos(['<h1', '<a', 'href=', '<img', 'src=']),
            4 => $contieneTodos(['<style', 'color:', 'background-color:', '<h1', '<p']),
            5 => $contieneTodos(['<style', '<div', 'width:', 'padding:', 'border:', 'margin:']),
            6 => $contieneTodos(['display:flex', 'gap', 'justify-content', 'padding', 'border', 'background-color']),
            7 => $contieneTodos(['display:grid', 'grid-template-columns', 'gap', 'padding', 'background-color', 'border-radius']),
            8 => (str_contains($lower, 'let ') || str_contains($lower, 'const '))
                && $contieneTodos(['if', 'getelementbyid', 'textcontent', 'addeventlistener']),
            9 => $contieneTodos(['getelementbyid', 'addeventlistener', 'click', 'textcontent'])
                && (str_contains($lower, 'classlist') || str_contains($lower, '.style')),
            10 => $contieneTodos(['submit', 'preventdefault', 'if', 'textcontent'])
                && (str_contains($lower, '.value') || str_contains($lower, 'value')),
            11 => str_contains($lower, 'bootstrap')
                && str_contains($lower, 'stylesheet')
                && preg_match('/class\s*=\s*["\'][^"\']*\bcontainer\b/i', $codigo) === 1
                && preg_match('/class\s*=\s*["\'][^"\']*\brow\b/i', $codigo) === 1
                && preg_match_all('/class\s*=\s*["\'][^"\']*\bcol(?:-[a-z0-9]+)?\b[^"\']*["\']/i', $codigo) >= 3
                && (str_contains($lower, 'card') || str_contains($lower, 'tarjeta')),
            12 => str_contains($lower, 'bootstrap')
                && str_contains($lower, 'stylesheet')
                && preg_match('/class\s*=\s*["\'][^"\']*\bcard\b/i', $codigo) === 1
                && preg_match('/class\s*=\s*["\'][^"\']*\bbtn\b/i', $codigo) === 1
                && preg_match('/class\s*=\s*["\'][^"\']*\bbadge\b/i', $codigo) === 1
                && preg_match('/class\s*=\s*["\'][^"\']*\balert\b/i', $codigo) === 1,
            13 => $this->phpBasicoValido($codigo, ['<?php', '$', 'echo', '?>']),
            14 => $contieneTodos(['<form', 'name=', '$_post', 'echo', 'nombre'])
                && (str_contains($compact, 'method="post"') || str_contains($compact, "method='post'") || str_contains($compact, 'method=post'))
                && (str_contains($lower, 'isset') || str_contains($lower, 'if')),
            15 => $contieneTodos(['<?php', 'session_start', '$_session', 'setcookie', 'echo']),
            16 => $this->codigoProyectoPersonalValido($codigo),
            17 => $this->codigoFormularioInteractivoValido($codigo),
            default => false,
        };
    }

    private function phpBasicoValido(string $codigo, array $fragmentos): bool
    {
        $lower = mb_strtolower($codigo, 'UTF-8');

        foreach ($fragmentos as $fragmento) {
            if (!str_contains($lower, mb_strtolower($fragmento, 'UTF-8'))) {
                return false;
            }
        }

        return $this->simularSalidaPhp($codigo) !== null;
    }

    private function simularSalidaPhp(string $codigo): ?string
    {
        $contenido = preg_replace(['/^\s*<\?php/i', '/\?>\s*$/'], '', trim($codigo)) ?? '';
        $variables = [];

        foreach (preg_split('/\R/', $contenido) ?: [] as $linea) {
            $linea = trim($linea);
            if (preg_match('/^\$([a-zA-Z_][a-zA-Z0-9_]*)\s*=\s*(["\'])(.*?)\2\s*;?$/', $linea, $coincidencia)) {
                $variables[$coincidencia[1]] = $coincidencia[3];
            }
        }

        foreach (preg_split('/\R/', $contenido) ?: [] as $linea) {
            $linea = trim($linea);
            if (preg_match('/^echo\s+(["\'])(.*?)\1\s*;?$/i', $linea, $coincidencia)) {
                return $coincidencia[2];
            }

            if (preg_match('/^echo\s+\$([a-zA-Z_][a-zA-Z0-9_]*)\s*;?$/i', $linea, $coincidencia)
                && array_key_exists($coincidencia[1], $variables)) {
                return $variables[$coincidencia[1]];
            }
        }

        return null;
    }

    private function codigoProyectoPersonalValido(string $codigo): bool
    {
        $lower = mb_strtolower($codigo, 'UTF-8');
        $projectCards = preg_match_all('/class\s*=\s*["\'][^"\']*(proyecto|project|card|tarjeta)[^"\']*["\']/i', $codigo);
        $articles = preg_match_all('/<article[\s>]/i', $codigo);

        return str_contains($lower, '<nav')
            && str_contains($lower, '<a')
            && str_contains($lower, 'href=')
            && (str_contains($lower, '<header') || str_contains($lower, 'class="hero') || str_contains($lower, "class='hero") || str_contains($lower, 'id="inicio') || str_contains($lower, "id='inicio"))
            && str_contains($lower, '<img')
            && str_contains($lower, 'src=')
            && str_contains($lower, 'alt=')
            && (str_contains($lower, 'sobre mí') || str_contains($lower, 'sobre mi') || str_contains($lower, 'about'))
            && substr_count($lower, '<ul') >= 1
            && substr_count($lower, '<li') >= 3
            && (($projectCards ?: 0) >= 2 || ($articles ?: 0) >= 2)
            && str_contains($lower, '<form')
            && str_contains($lower, '<input')
            && str_contains($lower, '<textarea')
            && str_contains($lower, '<button')
            && (str_contains($lower, 'background-color') || str_contains($lower, 'background:'))
            && str_contains($lower, 'color:')
            && str_contains($lower, 'padding')
            && str_contains($lower, 'border-radius')
            && (str_contains($lower, 'display: flex') || str_contains($lower, 'display:flex') || str_contains($lower, 'display: grid') || str_contains($lower, 'display:grid'))
            && (str_contains($lower, 'gap') || str_contains($lower, 'margin'))
            && (str_contains($lower, '@media') || str_contains($lower, 'flex-wrap') || str_contains($lower, 'grid-template-columns'))
            && (str_contains($lower, 'addeventlistener') || str_contains($lower, 'onclick=') || str_contains($lower, 'alert(') || str_contains($lower, 'textcontent'));
    }

    private function codigoFormularioInteractivoValido(string $codigo): bool
    {
        $lower = mb_strtolower($codigo, 'UTF-8');
        $compact = preg_replace('/\s+/', '', $lower) ?? '';

        return str_contains($lower, '<form')
            && str_contains($lower, '</form>')
            && str_contains($lower, '<label')
            && str_contains($lower, 'for=')
            && str_contains($lower, 'name=')
            && (str_contains($lower, 'type="email"') || str_contains($lower, "type='email'"))
            && str_contains($lower, 'required')
            && str_contains($lower, '<select')
            && str_contains($lower, '<textarea')
            && str_contains($lower, 'addeventlistener')
            && str_contains($lower, 'submit')
            && str_contains($lower, 'preventdefault')
            && str_contains($lower, 'value')
            && str_contains($lower, 'textcontent')
            && (str_contains($lower, 'background-color') || str_contains($lower, 'background:'))
            && str_contains($lower, 'border-radius')
            && (str_contains($compact, 'display:flex') || str_contains($compact, 'display:grid'));
    }
}
