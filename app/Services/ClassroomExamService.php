<?php

namespace App\Services;

use App\Models\TestResultadoSesion;
use App\Models\TestSesion;
use App\Models\TestSesionParticipante;
use App\Models\TestSesionPregunta;
use App\Models\TestSesionRespuesta;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class ClassroomExamService
{
    public function __construct(private readonly TestAnswerEvaluator $evaluator, private readonly TestHistoryService $history) {}

    public function estado(Usuario $student, TestSesion $session, ?int $selectedEmissionId = null): array
    {
        $participant = $this->authorize($student, $session);
        if ($session->estado === 'espera') {
            return $this->payload($session, $participant, null, null);
        }
        if (! in_array($session->estado, ['activa', 'finalizada', 'cerrada'], true)) {
            throw new \DomainException('El examen no está disponible.');
        }
        $this->initialize($session, $participant);
        if ($this->expired($session, $participant) && $participant->estado !== 'finalizado') {
            $this->submit($student, $session, true);
        }
        $participant->refresh();
        $result = $session->resultados()->where('participante_id', $participant->id)->first();
        $question = null;
        if ($participant->estado !== 'finalizado' && $session->estado === 'activa') {
            if ($selectedEmissionId) {
                $question = $session->preguntas()->with('pregunta')->whereKey($selectedEmissionId)->first();
                if (! $question) {
                    throw new \DomainException('La pregunta seleccionada no pertenece a este examen.');
                }
            } else {
                $question = $session->preguntas()->with('pregunta')->whereDoesntHave('respuestas', fn ($q) => $q->where('participante_id', $participant->id))->orderBy('ronda')->orderBy('orden')->first();
            }
        }

        return $this->payload($session->fresh(), $participant, $question, $result);
    }

    public function save(Usuario $student, TestSesion $session, TestSesionPregunta $emission, mixed $answer): array
    {
        if ($session->estado !== 'activa') {
            throw new \DomainException('El examen no está activo.');
        }
        $participant = $this->authorize($student, $session);
        if ($participant->estado === 'finalizado') {
            throw new \DomainException('El examen ya fue entregado.');
        }
        $this->initialize($session, $participant);
        if ($this->expired($session, $participant)) {
            $this->submit($student, $session, true);
            throw new \DomainException('El tiempo del examen terminó y fue entregado automáticamente.');
        }

        return DB::transaction(function () use ($student, $session, $emission, $answer) {
            $session = TestSesion::lockForUpdate()->findOrFail($session->id);
            $participant = $this->authorize($student, $session, true);
            if ($participant->estado === 'finalizado') {
                throw new \DomainException('El examen ya fue entregado.');
            }
            if ($session->estado !== 'activa') {
                throw new \DomainException('El examen no está activo.');
            }
            $this->initialize($session, $participant);
            if ($this->expired($session, $participant)) {
                throw new \DomainException('El tiempo del examen terminó. Actualiza la página para consultar la entrega automática.');
            }
            if ((int) $emission->sesion_id !== (int) $session->id) {
                throw new \DomainException('La pregunta no pertenece a este examen.');
            }
            $emission->loadMissing('pregunta');
            $correct = $this->evaluator->esCorrecta($emission->pregunta, $answer);
            TestSesionRespuesta::updateOrCreate(
                ['sesion_pregunta_id' => $emission->id, 'participante_id' => $participant->id],
                ['respuesta' => is_array($answer) ? $answer : [$answer], 'correcta' => $correct, 'tiempo_respuesta_ms' => null,
                    'orden_respuesta' => null, 'puntos_otorgados' => $correct ? $emission->puntos_maximos : 0, 'respondida_at' => now()]
            );
            $participant->forceFill(['ultima_actividad_at' => now()])->save();
            $this->updateProgress($session, $participant);

            return $this->estado($student, $session->fresh());
        });
    }

    public function submit(Usuario $student, TestSesion $session, bool $automatic = false): TestResultadoSesion
    {
        return DB::transaction(function () use ($student, $session) {
            $session = TestSesion::lockForUpdate()->findOrFail($session->id);
            $participant = $this->authorize($student, $session, true);
            $existing = $session->resultados()->where('participante_id', $participant->id)->first();
            if ($existing?->estado === 'entregado') {
                return $existing;
            }
            $this->initialize($session, $participant);
            $result = $this->updateProgress($session, $participant);
            $total = (int) $session->preguntas()->count();
            $correct = (int) $participant->respuestas()->whereHas('sesionPregunta', fn ($q) => $q->where('sesion_id', $session->id))->where('correcta', true)->count();
            $max = (int) $session->preguntas()->sum('puntos_maximos');
            $earned = (int) $session->preguntas()->join('test_sesion_respuestas as r', 'r.sesion_pregunta_id', '=', 'test_sesion_preguntas.id')
                ->where('r.participante_id', $participant->id)->sum('r.puntos_otorgados');
            $result->forceFill(['total_preguntas' => $total, 'correctas' => $correct, 'incorrectas' => max(0, $total - $correct),
                'calificacion' => $max > 0 ? round($earned * 100 / $max, 2) : 0, 'puntos_test' => $earned,
                'estado' => 'entregado', 'entregada_at' => now()])->save();
            $participant->forceFill(['puntos_test' => $earned, 'estado' => 'finalizado', 'finalizado_at' => now(), 'ultima_actividad_at' => now()])->save();

            return $result->fresh();
        });
    }

    public function submitPending(TestSesion $session): void
    {
        if ($session->modalidad !== 'examen') {
            return;
        }
        foreach ($session->participantes()->where('es_anfitrion', false)->where('estado', '!=', 'finalizado')->with('usuario')->get() as $participant) {
            if ($participant->usuario) {
                $this->submit($participant->usuario, $session, true);
            }
        }
    }

    private function initialize(TestSesion $session, TestSesionParticipante $participant): void
    {
        if (! $participant->examen_iniciado_at && $session->estado === 'activa') {
            $start = now();
            $minutes = (int) ($session->configuracion['duracion_examen_minutos'] ?? 0);
            $deadline = $minutes > 0 ? $start->copy()->addMinutes($minutes) : null;
            if ($session->disponible_hasta && (! $deadline || $session->disponible_hasta->lessThan($deadline))) {
                $deadline = $session->disponible_hasta->copy();
            }
            $participant->forceFill(['examen_iniciado_at' => $start, 'examen_limite_at' => $deadline, 'ultima_actividad_at' => now()])->save();
        }
        TestResultadoSesion::firstOrCreate(['sesion_id' => $session->id, 'participante_id' => $participant->id], [
            'usuario_id' => $participant->usuario_id, 'colegio_id' => $session->colegio_id, 'clase_id' => $session->clase_id,
            'nombre_alumno' => $participant->usuario()->first()?->nombreParaReportes() ?: $participant->nombre_visible, 'modalidad' => 'examen', 'total_preguntas' => $session->preguntas()->count(),
            'correctas' => 0, 'incorrectas' => 0, 'calificacion' => 0, 'puntos_test' => 0, 'estado' => 'en_progreso', 'iniciada_at' => now(),
        ]);
    }

    private function updateProgress(TestSesion $session, TestSesionParticipante $participant): TestResultadoSesion
    {
        $answers = TestSesionRespuesta::where('participante_id', $participant->id)->whereHas('sesionPregunta', fn ($q) => $q->where('sesion_id', $session->id));
        $answered = (clone $answers)->count();
        $correct = (clone $answers)->where('correcta', true)->count();
        $result = $session->resultados()->where('participante_id', $participant->id)->firstOrFail();
        $result->forceFill(['total_preguntas' => $session->preguntas()->count(), 'correctas' => $correct, 'incorrectas' => $answered - $correct])->save();

        return $result;
    }

    private function authorize(Usuario $student, TestSesion $session, bool $lock = false): TestSesionParticipante
    {
        if (! $student->esAlumno() || $session->tipo !== 'clase' || $session->modalidad !== 'examen' || (int) $student->colegio_id !== (int) $session->colegio_id
            || ! $student->clasesComoAlumno()->whereKey($session->clase_id)->exists()) {
            throw new \DomainException('No perteneces a la clase de este examen.');
        }
        $query = $session->participantes()->where('usuario_id', $student->id);
        if ($lock) {
            $query->lockForUpdate();
        }
        $participant = $query->first();
        if (! $participant) {
            throw new \DomainException('Primero debes ingresar a la sesión.');
        }

        return $participant;
    }

    private function expired(TestSesion $session, TestSesionParticipante $participant): bool
    {
        return ($participant->examen_limite_at && now()->greaterThanOrEqualTo($participant->examen_limite_at)) || ($session->disponible_hasta && now()->greaterThanOrEqualTo($session->disponible_hasta));
    }

    private function payload(TestSesion $session, TestSesionParticipante $participant, ?TestSesionPregunta $emission, ?TestResultadoSesion $result): array
    {
        $submitted = $participant->estado === 'finalizado' || $result?->estado === 'entregado';
        $deadline = $participant->examen_limite_at?->copy();
        if ($session->disponible_hasta && (! $deadline || $session->disponible_hasta->lessThan($deadline))) {
            $deadline = $session->disponible_hasta->copy();
        }
        $emissions = $session->preguntas()->with('pregunta')->orderBy('ronda')->orderBy('orden')->get();
        $responses = $participant->respuestas()->whereIn('sesion_pregunta_id', $emissions->pluck('id'))->get()->keyBy('sesion_pregunta_id');
        $currentIndex = $emission ? $emissions->search(fn ($item) => (int) $item->id === (int) $emission->id) : false;

        return ['sesion' => $session, 'participante' => $participant, 'pregunta' => $emission?->pregunta, 'emision' => $emission,
            'emisiones' => $emissions, 'respondidas_ids' => $responses->keys()->map(fn ($id) => (int) $id)->all(),
            'respuesta_actual' => $emission ? ($responses->get($emission->id)?->respuesta ?? []) : [],
            'indice_actual' => $currentIndex === false ? null : $currentIndex,
            'anterior' => $currentIndex !== false && $currentIndex > 0 ? $emissions[$currentIndex - 1] : null,
            'siguiente' => $currentIndex !== false && $currentIndex < $emissions->count() - 1 ? $emissions[$currentIndex + 1] : null,
            'respondidas' => $responses->count(),
            'total' => $emissions->count(), 'limite' => $deadline, 'entregado' => $submitted,
            'resultado' => $submitted ? $result : null];
    }
}
