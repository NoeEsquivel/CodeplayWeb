<?php

namespace App\Services;

use App\Models\TestSesion;
use App\Models\TestSesionParticipante;
use App\Models\TestSesionPregunta;
use App\Models\TestSesionRespuesta;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LiveTestEngineService
{
    public function __construct(
        private readonly TestAnswerEvaluator $evaluador,
        private readonly LiveTestScoringService $puntuacion,
        private readonly TestHistoryService $history,
    ) {}

    public function abrirSala(Usuario $actor, TestSesion $sesion): TestSesion
    {
        return DB::transaction(function () use ($actor, $sesion) {
            $sesion = TestSesion::lockForUpdate()->findOrFail($sesion->id);
            $this->autorizarAnfitrion($actor, $sesion);
            if ($sesion->estado !== 'configuracion') {
                throw new \DomainException('La sala ya fue abierta o cerrada.');
            }

            $sesion->forceFill(['estado' => 'espera', 'version' => $sesion->version + 1])->save();

            return $sesion->fresh();
        });
    }

    public function unir(Usuario $actor, string $codigo, ?string $palabraClave = null, ?string $tipoEsperado = null): array
    {
        $entrada = trim($codigo);
        $codigo = Str::upper($entrada);

        return DB::transaction(function () use ($actor, $entrada, $codigo, $palabraClave, $tipoEsperado) {
            $sesion = TestSesion::where('codigo_sala', $codigo)->lockForUpdate()->first();
            $accesoPorPalabra = false;
            if (! $sesion && $actor->colegio_id && $entrada !== '') {
                $candidatas = TestSesion::where('colegio_id', $actor->colegio_id)
                    ->where('estado', 'espera')->whereNotNull('palabra_clave_hash');
                if ($tipoEsperado) {
                    $candidatas->where('tipo', $tipoEsperado);
                }
                $coincidencias = $candidatas->get()
                    ->filter(fn (TestSesion $candidata) => Hash::check($entrada, $candidata->palabra_clave_hash));
                if ($coincidencias->count() > 1) {
                    throw new \DomainException('Esa palabra identifica varias salas. Ingresa el código de sala.');
                }
                if ($coincidencias->count() === 1) {
                    $sesion = TestSesion::whereKey($coincidencias->first()->id)->lockForUpdate()->first();
                    $accesoPorPalabra = true;
                }
            }
            $admiteExamenActivo = $sesion && $sesion->tipo === 'clase' && $sesion->modalidad === 'examen' && $sesion->estado === 'activa';
            if (! $sesion || ($sesion->estado !== 'espera' && ! $admiteExamenActivo)) {
                throw new \DomainException('La sala no existe o ya no admite participantes.');
            }
            if ($sesion->disponible_desde && now()->lessThan($sesion->disponible_desde)) {
                throw new \DomainException('Esta actividad todavía no está disponible.');
            }
            if ($sesion->disponible_hasta && now()->greaterThan($sesion->disponible_hasta)) {
                throw new \DomainException('El período para ingresar a esta actividad terminó.');
            }
            if ($tipoEsperado && $sesion->tipo !== $tipoEsperado) {
                throw new \DomainException('El acceso corresponde a otro tipo de actividad.');
            }
            $this->autorizarColegio($actor, $sesion);
            $this->autorizarClase($actor, $sesion);
            if ($sesion->palabra_clave_hash && ! $accesoPorPalabra
                && ! Hash::check((string) $palabraClave, $sesion->palabra_clave_hash)) {
                throw new \DomainException('La palabra clave de la sala no es correcta.');
            }

            $existente = $sesion->participantes()->where('usuario_id', $actor->id)->lockForUpdate()->first();
            $token = Str::random(64);
            if ($existente) {
                $existente->forceFill([
                    'reconexion_token_hash' => hash('sha256', $token),
                    'estado' => 'conectado',
                    'ultima_actividad_at' => now(),
                ])->save();

                return ['sesion' => $sesion->fresh(), 'participante' => $existente->fresh(), 'reconexion_token' => $token];
            }

            if ($sesion->participantes()->count() >= $sesion->max_participantes) {
                throw new \DomainException("La sala alcanzó su máximo de {$sesion->max_participantes} participantes.");
            }

            $participante = $sesion->participantes()->create([
                'usuario_id' => $actor->id,
                'nombre_visible' => $actor->nombre,
                'reconexion_token_hash' => hash('sha256', $token),
                'estado' => 'conectado',
                'es_anfitrion' => (int) $sesion->anfitrion_usuario_id === (int) $actor->id,
                'puntos_test' => 0,
                'unido_at' => now(),
                'ultima_actividad_at' => now(),
            ]);
            $sesion->increment('version');

            return ['sesion' => $sesion->fresh(), 'participante' => $participante, 'reconexion_token' => $token];
        });
    }

    public function reconectar(Usuario $actor, TestSesion $sesion, string $token): array
    {
        return DB::transaction(function () use ($actor, $sesion, $token) {
            $sesion = TestSesion::lockForUpdate()->findOrFail($sesion->id);
            $this->autorizarColegio($actor, $sesion);
            if (in_array($sesion->estado, ['cerrada'], true)) {
                throw new \DomainException('La sesión ya fue cerrada.');
            }

            $participante = $sesion->participantes()->where('usuario_id', $actor->id)->lockForUpdate()->first();
            if (! $participante || ! hash_equals($participante->reconexion_token_hash, hash('sha256', $token))) {
                throw new \DomainException('El enlace de reconexión no es válido.');
            }

            $nuevoToken = Str::random(64);
            $participante->forceFill([
                'reconexion_token_hash' => hash('sha256', $nuevoToken),
                'estado' => 'conectado',
                'ultima_actividad_at' => now(),
            ])->save();

            return ['participante' => $participante->fresh(), 'reconexion_token' => $nuevoToken];
        });
    }

    public function iniciar(Usuario $actor, TestSesion $sesion): TestSesion
    {
        return DB::transaction(function () use ($actor, $sesion) {
            $sesion = TestSesion::lockForUpdate()->findOrFail($sesion->id);
            $this->autorizarAnfitrion($actor, $sesion);
            if ($sesion->estado !== 'espera') {
                throw new \DomainException('La sala no está en espera.');
            }
            if ($sesion->disponible_desde && now()->lessThan($sesion->disponible_desde)) {
                throw new \DomainException('La actividad aún no está disponible.');
            }
            if ($sesion->disponible_hasta && now()->greaterThan($sesion->disponible_hasta)) {
                throw new \DomainException('La actividad ya no está disponible.');
            }
            if ($sesion->modalidad !== 'examen' && $sesion->participantes()->count() < 1) {
                throw new \DomainException('Debe ingresar al menos un participante.');
            }

            if (! $sesion->preguntas()->exists()) {
                $this->crearRonda($sesion, 1);
            }
            if (! $sesion->preguntas()->exists()) {
                throw new \DomainException('Los bancos seleccionados no tienen preguntas disponibles.');
            }

            $sesion->forceFill([
                'estado' => 'activa',
                'pregunta_actual_indice' => 1,
                'iniciada_at' => $sesion->iniciada_at ?? now(),
                'version' => $sesion->version + 1,
            ])->save();
            if ($sesion->modalidad !== 'examen') {
                $this->abrirPregunta($sesion, $sesion->preguntas()->orderBy('ronda')->orderBy('orden')->firstOrFail());
            }

            return $sesion->fresh();
        });
    }

    public function responder(Usuario $actor, TestSesion $sesion, mixed $respuesta): array
    {
        if ($sesion->modalidad === 'examen') {
            throw new \DomainException('Las respuestas del examen usan el guardado individual.');
        }
        $this->sincronizar($sesion);

        $resultado = DB::transaction(function () use ($actor, $sesion, $respuesta) {
            $sesion = TestSesion::lockForUpdate()->findOrFail($sesion->id);
            $participante = $this->participanteAutorizado($actor, $sesion, true);
            $emision = $this->preguntaActual($sesion, true);

            if ($sesion->estado !== 'activa' || ! $emision || $emision->estado !== 'abierta') {
                return ['error' => 'La pregunta ya no está abierta.'];
            }
            if (! $emision->cierra_at || now()->greaterThan($emision->cierra_at)) {
                $this->publicarResultado($sesion, $emision);

                return ['error' => 'El tiempo para responder terminó.'];
            }
            if ($emision->respuestas()->where('participante_id', $participante->id)->exists()) {
                return ['error' => 'Ya registramos tu respuesta para esta pregunta.'];
            }

            $orden = (int) $emision->respuestas()->max('orden_respuesta') + 1;
            $correcta = $this->evaluador->esCorrecta($emision->pregunta, $respuesta);
            $totalParticipantes = max(1, $sesion->participantes()->count());
            $puntos = $this->puntuacion->puntosPorOrden($correcta, $orden, $totalParticipantes);
            $inicioMs = $emision->abierta_at?->getTimestampMs() ?? now()->getTimestampMs();

            $registro = TestSesionRespuesta::create([
                'sesion_pregunta_id' => $emision->id,
                'participante_id' => $participante->id,
                'respuesta' => is_array($respuesta) ? $respuesta : [$respuesta],
                'correcta' => $correcta,
                'tiempo_respuesta_ms' => max(0, now()->getTimestampMs() - $inicioMs),
                'orden_respuesta' => $orden,
                'puntos_otorgados' => $puntos,
                'respondida_at' => now(),
            ]);
            $participante->forceFill([
                'puntos_test' => $participante->puntos_test + $puntos,
                'ultima_actividad_at' => now(),
            ])->save();
            $sesion->increment('version');

            if ($emision->respuestas()->count() >= $totalParticipantes) {
                $this->publicarResultado($sesion->fresh(), $emision->fresh());
            }

            return ['respuesta' => $registro, 'puntos' => $puntos, 'correcta' => $correcta];
        });

        if (isset($resultado['error'])) {
            throw new \DomainException($resultado['error']);
        }

        return $resultado;
    }

    public function estado(Usuario $actor, TestSesion $sesion): array
    {
        $sesion = $this->sincronizar($sesion);
        $participante = $this->participanteAutorizado($actor, $sesion, false);
        $esAnfitrion = (int) $sesion->anfitrion_usuario_id === (int) $actor->id;
        if (! $participante && ! $esAnfitrion) {
            throw new \DomainException('No perteneces a esta sesión.');
        }
        if ($participante && ! in_array($sesion->estado, ['finalizada', 'cerrada'], true)) {
            $participante->forceFill(['estado' => 'conectado', 'ultima_actividad_at' => now()])->save();
        }

        $emision = $this->preguntaActual($sesion, false);
        $mostrarResultado = $emision && $emision->estado === 'resultado';
        $respuesta = $participante && $emision
            ? $emision->respuestas()->where('participante_id', $participante->id)->first()
            : null;

        $clasificacion = $sesion->modalidad === 'examen' ? [] : $this->clasificacion($sesion);
        $ganador = in_array($sesion->estado, ['finalizada', 'cerrada'], true)
            && (int) ($clasificacion[0]['puntos'] ?? 0) > 0
                ? $clasificacion[0]
                : null;

        return [
            'sesion' => [
                'id' => $sesion->id,
                'codigo' => $sesion->codigo_sala,
                'estado' => $sesion->estado,
                'version' => $sesion->version,
                'ronda' => $emision?->ronda,
                'pregunta_numero' => $sesion->pregunta_actual_indice,
                'puntaje_objetivo' => $sesion->puntaje_objetivo,
                'es_anfitrion' => $esAnfitrion,
            ],
            'fase' => $sesion->estado === 'espera' ? 'espera' : ($mostrarResultado ? 'resultado' : $sesion->estado),
            'participantes' => $sesion->participantes()->count(),
            'pregunta' => $emision ? [
                'id' => $emision->id,
                'enunciado' => $emision->pregunta->enunciado,
                'tipo' => $emision->pregunta->tipo,
                'opciones' => $emision->pregunta->opciones,
                'segundos_restantes' => $emision->estado === 'abierta' && $emision->cierra_at
                    ? max(0, now()->diffInSeconds($emision->cierra_at, false))
                    : 0,
                'respuestas_correctas' => $mostrarResultado ? $emision->pregunta->respuestas_correctas : null,
                'explicacion' => $mostrarResultado ? $emision->pregunta->explicacion : null,
            ] : null,
            'mi_resultado' => $participante ? [
                'respondio' => (bool) $respuesta,
                'correcta' => $mostrarResultado ? (bool) ($respuesta?->correcta ?? false) : null,
                'puntos_sumados' => $mostrarResultado ? (int) ($respuesta?->puntos_otorgados ?? 0) : null,
                'puntos_totales' => (int) $participante->fresh()->puntos_test,
                'orden_respuesta' => $mostrarResultado ? $respuesta?->orden_respuesta : null,
            ] : null,
            'clasificacion' => $clasificacion,
            'ganador' => $ganador,
        ];
    }

    public function pausar(Usuario $actor, TestSesion $sesion): TestSesion
    {
        $this->sincronizar($sesion);

        return DB::transaction(function () use ($actor, $sesion) {
            $sesion = TestSesion::lockForUpdate()->findOrFail($sesion->id);
            $this->autorizarAnfitrion($actor, $sesion);
            if ($sesion->estado !== 'activa') {
                throw new \DomainException('Solo se puede pausar una partida activa.');
            }
            $emision = $this->preguntaActual($sesion, true);
            if (! $emision || $emision->estado !== 'abierta') {
                throw new \DomainException('Espera a que comience la siguiente pregunta para pausar.');
            }

            $configuracion = $sesion->configuracion ?? [];
            $configuracion['pausa_restante'] = max(1, now()->diffInSeconds($emision->cierra_at, false));
            $sesion->forceFill([
                'estado' => 'pausada',
                'configuracion' => $configuracion,
                'version' => $sesion->version + 1,
            ])->save();

            return $sesion->fresh();
        });
    }

    public function reanudar(Usuario $actor, TestSesion $sesion): TestSesion
    {
        return DB::transaction(function () use ($actor, $sesion) {
            $sesion = TestSesion::lockForUpdate()->findOrFail($sesion->id);
            $this->autorizarAnfitrion($actor, $sesion);
            if ($sesion->estado !== 'pausada') {
                throw new \DomainException('La partida no está pausada.');
            }
            $emision = $this->preguntaActual($sesion, true);
            if (! $emision || $emision->estado !== 'abierta') {
                throw new \DomainException('No hay una pregunta para reanudar.');
            }

            $configuracion = $sesion->configuracion ?? [];
            $restante = max(1, min(300, (int) ($configuracion['pausa_restante'] ?? $sesion->tiempo_pregunta ?? 20)));
            unset($configuracion['pausa_restante']);
            $emision->forceFill(['cierra_at' => now()->addSeconds($restante)])->save();
            $sesion->forceFill([
                'estado' => 'activa',
                'configuracion' => $configuracion,
                'version' => $sesion->version + 1,
            ])->save();

            return $sesion->fresh();
        });
    }

    public function repetir(Usuario $actor, TestSesion $sesion, bool $paraReconfigurar = false): TestSesion
    {
        return DB::transaction(function () use ($actor, $sesion, $paraReconfigurar) {
            $sesion = TestSesion::lockForUpdate()->findOrFail($sesion->id);
            $this->autorizarAnfitrion($actor, $sesion);
            if (! in_array($sesion->estado, ['finalizada', 'cerrada'], true)) {
                throw new \DomainException('La partida debe haber terminado antes de repetirla.');
            }

            $this->history->snapshot($sesion);

            $sesion->preguntas()->each(function (TestSesionPregunta $emision) {
                $emision->respuestas()->delete();
                $emision->delete();
            });
            $sesion->participantes()->update([
                'estado' => 'conectado', 'puntos_test' => 0,
                'finalizado_at' => null, 'ultima_actividad_at' => now(),
            ]);
            $configuracion = $sesion->configuracion ?? [];
            unset($configuracion['pausa_restante']);
            $configuracion['intento_actual'] = max(1, (int) ($configuracion['intento_actual'] ?? 1)) + 1;
            $sesion->forceFill([
                'estado' => $paraReconfigurar ? 'configuracion' : 'espera',
                'pregunta_actual_indice' => 0,
                'configuracion' => $configuracion,
                'iniciada_at' => null,
                'finalizada_at' => null,
                'cerrada_at' => null,
                'version' => $sesion->version + 1,
            ])->save();

            return $sesion->fresh();
        });
    }

    public function reconfigurar(Usuario $actor, TestSesion $sesion, array $datos, array $bancos): TestSesion
    {
        return DB::transaction(function () use ($actor, $sesion, $datos, $bancos) {
            $sesion = TestSesion::lockForUpdate()->findOrFail($sesion->id);
            $this->autorizarAnfitrion($actor, $sesion);
            if ($sesion->estado !== 'configuracion') {
                throw new \DomainException('La sala no está en configuración.');
            }
            foreach ($bancos as $banco) {
                app(LiveTestSessionService::class)->validarBancoAccesible($actor, $banco, 'amigos');
            }

            $configuracion = [
                'segundos_resultado' => 5,
                'orden_aleatorio' => (bool) ($datos['orden_aleatorio'] ?? false),
                'intento_actual' => max(1, (int) (($sesion->configuracion ?? [])['intento_actual'] ?? 1)),
            ];
            $sesion->bancos()->sync(collect($bancos)->values()->mapWithKeys(
                fn ($banco, $indice) => [$banco->id => ['orden' => $indice + 1]]
            )->all());
            $sesion->forceFill([
                'palabra_clave_hash' => Hash::make((string) $datos['palabra_clave']),
                'tiempo_pregunta' => (int) $datos['tiempo_pregunta'],
                'puntaje_objetivo' => (int) $datos['puntaje_objetivo'],
                'configuracion' => $configuracion,
                'estado' => 'espera',
                'version' => $sesion->version + 1,
            ])->save();

            return $sesion->fresh();
        });
    }

    public function cerrar(Usuario $actor, TestSesion $sesion): TestSesion
    {
        return DB::transaction(function () use ($actor, $sesion) {
            $sesion = TestSesion::lockForUpdate()->findOrFail($sesion->id);
            $this->autorizarAnfitrion($actor, $sesion);
            if ($sesion->estado === 'cerrada') {
                return $sesion;
            }

            $sesion->preguntas()->whereIn('estado', ['pendiente', 'abierta', 'resultado'])
                ->update(['estado' => 'cerrada', 'cerrada_at' => now()]);
            $sesion->participantes()->where('estado', '!=', 'finalizado')
                ->update(['estado' => 'finalizado', 'finalizado_at' => now(), 'ultima_actividad_at' => now()]);
            $sesion->forceFill(['estado' => 'cerrada', 'cerrada_at' => now(), 'version' => $sesion->version + 1])->save();
            $this->history->snapshot($sesion->fresh());

            return $sesion->fresh();
        });
    }

    public function sincronizar(TestSesion $sesion): TestSesion
    {
        if ($sesion->modalidad === 'examen') {
            return $sesion->fresh();
        }

        return DB::transaction(function () use ($sesion) {
            $sesion = TestSesion::lockForUpdate()->findOrFail($sesion->id);
            if ($sesion->estado !== 'activa') {
                return $sesion;
            }
            $emision = $this->preguntaActual($sesion, true);
            if (! $emision) {
                return $this->finalizarNaturalmente($sesion);
            }

            if ($emision->estado === 'abierta') {
                $todosRespondieron = $emision->respuestas()->count() >= $sesion->participantes()->count();
                if ($todosRespondieron || ($emision->cierra_at && now()->greaterThanOrEqualTo($emision->cierra_at))) {
                    $this->publicarResultado($sesion, $emision);
                }

                return $sesion->fresh();
            }

            if ($emision->estado === 'resultado' && $emision->cerrada_at
                && now()->greaterThanOrEqualTo($emision->cerrada_at->copy()->addSeconds($this->segundosResultado($sesion)))) {
                return $this->avanzar($sesion, $emision);
            }

            return $sesion;
        });
    }

    private function avanzar(TestSesion $sesion, TestSesionPregunta $actual): TestSesion
    {
        $actual->forceFill(['estado' => 'cerrada'])->save();
        if ($sesion->puntaje_objetivo && $sesion->participantes()->where('puntos_test', '>=', $sesion->puntaje_objetivo)->exists()) {
            return $this->finalizarNaturalmente($sesion);
        }

        $siguiente = $sesion->preguntas()->where('estado', 'pendiente')->orderBy('ronda')->orderBy('orden')->first();
        if (! $siguiente && $sesion->puntaje_objetivo) {
            $ronda = ((int) $sesion->preguntas()->max('ronda')) + 1;
            $this->crearRonda($sesion, $ronda);
            $siguiente = $sesion->preguntas()->where('ronda', $ronda)->orderBy('orden')->first();
        }
        if (! $siguiente) {
            return $this->finalizarNaturalmente($sesion);
        }

        $sesion->forceFill([
            'pregunta_actual_indice' => $sesion->pregunta_actual_indice + 1,
            'version' => $sesion->version + 1,
        ])->save();
        $this->abrirPregunta($sesion, $siguiente);

        return $sesion->fresh();
    }

    private function crearRonda(TestSesion $sesion, int $ronda): void
    {
        $preguntas = $sesion->bancos()->with(['preguntas' => fn ($q) => $q->where('activa', true)])
            ->get()->pluck('preguntas')->flatten()->values();
        if ((bool) ($sesion->configuracion['orden_aleatorio'] ?? true)) {
            $preguntas = $preguntas->shuffle()->values();
        }
        foreach ($preguntas as $indice => $pregunta) {
            $puntajes = (array) ($sesion->configuracion['puntos_pregunta'] ?? []);
            $sesion->preguntas()->create([
                'pregunta_id' => $pregunta->id,
                'ronda' => $ronda,
                'orden' => $indice + 1,
                'puntos_maximos' => max(1, min(1000, (int) ($puntajes[(string) $pregunta->id] ?? $pregunta->puntos ?? 1))),
                'estado' => 'pendiente',
            ]);
        }
    }

    private function abrirPregunta(TestSesion $sesion, TestSesionPregunta $emision): void
    {
        $segundos = max(5, min(300, (int) ($sesion->tiempo_pregunta ?? 20)));
        $ahora = now();
        $emision->forceFill([
            'estado' => 'abierta',
            'abierta_at' => $ahora,
            'cierra_at' => $ahora->copy()->addSeconds($segundos),
            'cerrada_at' => null,
        ])->save();
    }

    private function publicarResultado(TestSesion $sesion, TestSesionPregunta $emision): void
    {
        if ($emision->estado !== 'abierta') {
            return;
        }
        $emision->forceFill(['estado' => 'resultado', 'cerrada_at' => now()])->save();
        $sesion->forceFill(['version' => $sesion->version + 1])->save();
    }

    private function finalizarNaturalmente(TestSesion $sesion): TestSesion
    {
        $sesion->forceFill([
            'estado' => 'finalizada',
            'finalizada_at' => now(),
            'version' => $sesion->version + 1,
        ])->save();
        $sesion->participantes()->update(['estado' => 'finalizado', 'finalizado_at' => now(), 'ultima_actividad_at' => now()]);
        $this->history->snapshot($sesion->fresh());

        return $sesion->fresh();
    }

    private function preguntaActual(TestSesion $sesion, bool $bloquear): ?TestSesionPregunta
    {
        $consulta = $sesion->preguntas()->with('pregunta')
            ->whereIn('estado', ['abierta', 'resultado'])
            ->orderByDesc('ronda')->orderByDesc('orden');
        if ($bloquear) {
            $consulta->lockForUpdate();
        }

        return $consulta->first();
    }

    private function clasificacion(TestSesion $sesion): array
    {
        return $sesion->participantes()->select(['id', 'nombre_visible', 'puntos_test', 'estado', 'ultima_actividad_at'])
            ->orderByDesc('puntos_test')->orderBy('unido_at')->orderBy('id')->get()
            ->values()->map(fn ($participante, $indice) => [
                'posicion' => $indice + 1,
                'participante_id' => $participante->id,
                'nombre' => $participante->nombre_visible,
                'puntos' => (int) $participante->puntos_test,
                'conectado' => $participante->estado === 'conectado'
                    && $participante->ultima_actividad_at
                    && $participante->ultima_actividad_at->greaterThanOrEqualTo(now()->subSeconds(15)),
            ])->all();
    }

    private function participanteAutorizado(Usuario $actor, TestSesion $sesion, bool $requerido): ?TestSesionParticipante
    {
        $this->autorizarColegio($actor, $sesion);
        $participante = $sesion->participantes()->where('usuario_id', $actor->id)->first();
        if ($requerido && ! $participante) {
            throw new \DomainException('No perteneces a esta sesión.');
        }

        return $participante;
    }

    private function autorizarAnfitrion(Usuario $actor, TestSesion $sesion): void
    {
        $this->autorizarColegio($actor, $sesion);
        if ((int) $sesion->anfitrion_usuario_id !== (int) $actor->id) {
            throw new \DomainException('Solo el anfitrión puede realizar esta acción.');
        }
    }

    private function autorizarColegio(Usuario $actor, TestSesion $sesion): void
    {
        if (! $actor->colegio_id || (int) $actor->colegio_id !== (int) $sesion->colegio_id) {
            throw new \DomainException('La sesión no pertenece a tu colegio.');
        }
    }

    private function autorizarClase(Usuario $actor, TestSesion $sesion): void
    {
        if ($sesion->tipo === 'clase' && (! $actor->esAlumno()
            || ! $actor->clasesComoAlumno()->whereKey($sesion->clase_id)->exists())) {
            throw new \DomainException('No perteneces a la clase de esta sesión.');
        }
    }

    private function segundosResultado(TestSesion $sesion): int
    {
        return max(2, min(20, (int) ($sesion->configuracion['segundos_resultado'] ?? 5)));
    }
}
