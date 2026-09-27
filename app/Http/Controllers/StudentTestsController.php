<?php

namespace App\Http\Controllers;

use App\Models\TestBanco;
use App\Models\TestResultadoSesion;
use App\Models\TestSesion;
use App\Models\TestSesionParticipante;
use App\Models\Usuario;
use App\Services\InteractiveTestRules;
use App\Services\LiveTestSessionService;
use App\Services\TestAnswerEvaluator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StudentTestsController extends Controller
{
    private const CATEGORIAS = [
        'html' => [
            'nombre' => 'Fundamentos HTML',
            'descripcion' => 'Estructura, etiquetas, enlaces, imágenes y formularios.',
            'icono' => 'fa-code',
            'color' => 'html',
        ],
        'css' => [
            'nombre' => 'Diseño visual con CSS',
            'descripcion' => 'Selectores, colores, tipografía, cajas y diseños flexibles.',
            'icono' => 'fa-palette',
            'color' => 'css',
        ],
        'javascript' => [
            'nombre' => 'JavaScript básico',
            'descripcion' => 'Variables, decisiones, eventos e interactividad.',
            'icono' => 'fa-bolt',
            'color' => 'javascript',
        ],
        'mundos' => [
            'nombre' => 'Mundos de programación',
            'descripcion' => 'Lenguajes, software, bases de datos, robótica y videojuegos.',
            'icono' => 'fa-globe',
            'color' => 'mundos',
        ],
        'completo' => [
            'nombre' => 'Test CodePlay completo',
            'descripcion' => 'Preguntas combinadas de todas las dificultades anteriores.',
            'icono' => 'fa-layer-group',
            'color' => 'completo',
        ],
    ];

    public function index(): View
    {
        return view('alumno.tests');
    }

    public function codeplay(): View
    {
        $categorias = collect(self::CATEGORIAS)->map(function (array $categoria, string $clave) {
            $query = TestBanco::query()->where('origen', 'sistema')->where('activo', true);
            $clave === 'completo'
                ? $query->whereIn('categoria', ['html', 'css', 'javascript', 'mundos'])
                : $query->where('categoria', $clave);
            $categoria['clave'] = $clave;
            $categoria['preguntas'] = $query->withCount(['preguntas' => fn ($q) => $q->where('activa', true)])
                ->get()->sum('preguntas_count');
            return $categoria;
        })->values();

        return view('alumno.tests-codeplay', compact('categorias'));
    }

    public function iniciar(Request $request, LiveTestSessionService $sesiones, InteractiveTestRules $reglas): RedirectResponse
    {
        $categoria = (string) $request->validate(['categoria' => ['required', 'string', 'in:html,css,javascript,mundos,completo']])['categoria'];
        $usuario = Usuario::findOrFail((int) session('usuario_id'));

        $bancosQuery = TestBanco::query()->where('origen', 'sistema')->where('activo', true);
        $categoria === 'completo'
            ? $bancosQuery->whereIn('categoria', ['html', 'css', 'javascript', 'mundos'])
            : $bancosQuery->where('categoria', $categoria);
        $bancos = $bancosQuery->with(['preguntas' => fn ($q) => $q->where('activa', true)])->get();
        $preguntas = $bancos->pluck('preguntas')->flatten()->shuffle()->take(10)->values();

        if ($preguntas->count() < 10) {
            return back()->with('error', 'Esta categoría todavía no tiene diez preguntas disponibles.');
        }

        $sesion = DB::transaction(function () use ($sesiones, $reglas, $usuario, $categoria, $bancos, $preguntas) {
            $sesion = $sesiones->crear($usuario, [
                'tipo' => 'practica',
                'modalidad' => 'practica',
                'max_participantes' => 1,
                'configuracion' => ['categoria' => $categoria, 'cantidad' => 10, 'otorga_puntos_codeplay' => false],
            ], $bancos->all());

            TestSesionParticipante::create([
                'sesion_id' => $sesion->id,
                'usuario_id' => $usuario->id,
                'nombre_visible' => $usuario->nombre,
                'reconexion_token_hash' => hash('sha256', Str::random(64)),
                'estado' => 'conectado',
                'es_anfitrion' => true,
                'puntos_test' => 0,
                'unido_at' => now(),
                'ultima_actividad_at' => now(),
            ]);

            foreach ($preguntas as $indice => $pregunta) {
                $sesion->preguntas()->create([
                    'pregunta_id' => $pregunta->id,
                    'ronda' => 1,
                    'orden' => $indice + 1,
                    'estado' => 'pendiente',
                ]);
            }

            $reglas->transicionar($sesion, 'espera');
            $reglas->transicionar($sesion, 'activa');

            return $sesion;
        });

        return redirect()->route('alumno.tests.codeplay.jugar', $sesion);
    }

    public function jugar(TestSesion $sesion): View|RedirectResponse
    {
        [$usuario, $participante] = $this->autorizarPractica($sesion);
        $emision = $sesion->preguntas()
            ->whereDoesntHave('respuestas', fn ($q) => $q->where('participante_id', $participante->id))
            ->with('pregunta')
            ->orderBy('orden')
            ->first();

        if (!$emision) return $this->finalizar($sesion, $participante, $usuario);

        $emision->forceFill(['estado' => 'abierta', 'abierta_at' => $emision->abierta_at ?? now()])->save();
        $respondidas = $participante->respuestas()->count();
        $conTiempo = (bool) ($sesion->configuracion['con_tiempo'] ?? false);
        $limite = (int) ($emision->pregunta->tiempo_limite ?? 20);
        $venceEn = $emision->abierta_at?->copy()->addSeconds($limite)->getTimestamp() ?? (now()->getTimestamp() + $limite);
        $segundosRestantes = $conTiempo ? max(0, $venceEn - now()->getTimestamp()) : null;

        return view('alumno.tests-codeplay-pregunta', [
            'sesion' => $sesion,
            'emision' => $emision,
            'pregunta' => $emision->pregunta,
            'numero' => $respondidas + 1,
            'total' => $sesion->preguntas()->count(),
            'categoria' => $sesion->configuracion['nombre'] ?? (self::CATEGORIAS[$sesion->configuracion['categoria'] ?? 'completo']['nombre'] ?? 'Test CodePlay'),
            'conTiempo' => $conTiempo,
            'segundosRestantes' => $segundosRestantes,
        ]);
    }

    public function responder(Request $request, TestSesion $sesion, TestAnswerEvaluator $evaluador): RedirectResponse
    {
        [, $participante] = $this->autorizarPractica($sesion);
        $respuesta = $request->input('respuesta');
        if ($respuesta === null || $respuesta === '' || $respuesta === []) {
            return back()->with('error', 'Selecciona o escribe una respuesta antes de continuar.');
        }
        $valores = is_array($respuesta) ? $respuesta : [$respuesta];
        if (count($valores) > 10 || collect($valores)->contains(fn ($valor) => !is_scalar($valor) || mb_strlen((string) $valor) > 500)) {
            abort(422, 'La respuesta enviada no es válida.');
        }

        DB::transaction(function () use ($sesion, $participante, $respuesta, $evaluador) {
            $emision = $sesion->preguntas()
                ->whereDoesntHave('respuestas', fn ($q) => $q->where('participante_id', $participante->id))
                ->with('pregunta')
                ->orderBy('orden')
                ->lockForUpdate()
                ->firstOrFail();

            $conTiempo = (bool) ($sesion->configuracion['con_tiempo'] ?? false);
            $limite = (int) ($emision->pregunta->tiempo_limite ?? 20);
            $agotado = $conTiempo && $emision->abierta_at && now()->greaterThan($emision->abierta_at->copy()->addSeconds($limite + 1));
            $correcta = !$agotado && $evaluador->esCorrecta($emision->pregunta, $respuesta);
            $inicio = $emision->abierta_at?->getTimestampMs() ?? now()->getTimestampMs();
            $participante->respuestas()->create([
                'sesion_pregunta_id' => $emision->id,
                'respuesta' => $respuesta,
                'correcta' => $correcta,
                'tiempo_respuesta_ms' => max(0, now()->getTimestampMs() - $inicio),
                'orden_respuesta' => 1,
                'puntos_otorgados' => 0,
                'respondida_at' => now(),
            ]);
            $emision->forceFill(['estado' => 'cerrada', 'cerrada_at' => now()])->save();
            $participante->forceFill(['ultima_actividad_at' => now()])->save();
        });

        return redirect()->route('alumno.tests.codeplay.jugar', $sesion);
    }

    public function resultado(TestSesion $sesion): View|RedirectResponse
    {
        [, $participante] = $this->autorizarPractica($sesion, true);
        $resultado = TestResultadoSesion::where('sesion_id', $sesion->id)
            ->where('participante_id', $participante->id)
            ->first();

        if (!$resultado) return redirect()->route('alumno.tests.codeplay.jugar', $sesion);

        $detalles = $participante->respuestas()
            ->with('sesionPregunta.pregunta')
            ->orderBy('id')
            ->get()
            ->map(function ($respuesta) {
                $pregunta = $respuesta->sesionPregunta->pregunta;
                return [
                    'pregunta' => $pregunta->enunciado,
                    'correcta' => $respuesta->correcta,
                    'respuesta' => $this->mostrarRespuesta($respuesta->respuesta, $pregunta->opciones),
                    'esperada' => $this->mostrarRespuesta($pregunta->respuestas_correctas, $pregunta->opciones),
                    'explicacion' => $pregunta->explicacion,
                ];
            });

        $bancoPersonalId = ($sesion->configuracion['origen'] ?? null) === 'banco_personal'
            ? (int) ($sesion->configuracion['banco_id'] ?? 0)
            : null;

        return view('alumno.tests-codeplay-resultado', compact('sesion', 'resultado', 'detalles', 'bancoPersonalId'));
    }

    private function finalizar(TestSesion $sesion, TestSesionParticipante $participante, Usuario $usuario): RedirectResponse
    {
        DB::transaction(function () use ($sesion, $participante, $usuario) {
            $respuestas = $participante->respuestas;
            $correctas = $respuestas->where('correcta', true)->count();
            $total = $respuestas->count();

            TestResultadoSesion::firstOrCreate(
                ['sesion_id' => $sesion->id, 'participante_id' => $participante->id],
                [
                    'usuario_id' => $usuario->id,
                    'colegio_id' => $usuario->colegio_id,
                    'clase_id' => null,
                    'nombre_alumno' => $usuario->nombre,
                    'modalidad' => 'practica',
                    'total_preguntas' => $total,
                    'correctas' => $correctas,
                    'incorrectas' => $total - $correctas,
                    'calificacion' => $total > 0 ? round(($correctas / $total) * 100, 2) : 0,
                    'puntos_test' => 0,
                    'estado' => 'entregado',
                    'iniciada_at' => $sesion->iniciada_at,
                    'entregada_at' => now(),
                ]
            );

            if ($sesion->estado === 'activa') {
                $sesion->forceFill(['estado' => 'finalizada', 'finalizada_at' => now(), 'version' => $sesion->version + 1])->save();
            }
            $participante->forceFill(['estado' => 'finalizado', 'finalizado_at' => now(), 'ultima_actividad_at' => now()])->save();
        });

        return redirect()->route('alumno.tests.codeplay.resultado', $sesion);
    }

    private function autorizarPractica(TestSesion $sesion, bool $permitirFinalizada = false): array
    {
        $usuario = Usuario::findOrFail((int) session('usuario_id'));
        abort_unless(
            $sesion->tipo === 'practica'
            && $sesion->modalidad === 'practica'
            && (int) $sesion->colegio_id === (int) $usuario->colegio_id
            && ($sesion->estado === 'activa' || ($permitirFinalizada && $sesion->estado === 'finalizada')),
            404
        );

        $participante = $sesion->participantes()->where('usuario_id', $usuario->id)->firstOrFail();
        return [$usuario, $participante];
    }

    private function mostrarRespuesta(mixed $respuesta, ?array $opciones): string
    {
        $valores = is_array($respuesta) ? $respuesta : [$respuesta];
        return collect($valores)->map(function ($valor) use ($opciones) {
            $clave = (string) $valor;
            return $opciones[$clave] ?? match (strtolower($clave)) {
                '1', 'true' => 'Verdadero',
                '0', 'false' => 'Falso',
                default => $clave,
            };
        })->implode(', ');
    }
}
