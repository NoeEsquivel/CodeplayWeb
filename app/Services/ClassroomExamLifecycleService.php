<?php

namespace App\Services;

use App\Models\TestSesion;
use App\Models\Usuario;
use Illuminate\Support\Carbon;

class ClassroomExamLifecycleService
{
    public function __construct(
        private readonly LiveTestEngineService $engine,
        private readonly ClassroomExamService $exams,
    ) {}

    public function synchronize(TestSesion $session): TestSesion
    {
        $session = $session->fresh();
        if ($session->tipo !== 'clase' || $session->modalidad !== 'examen') {
            return $session;
        }
        if (in_array($session->estado, ['cerrada', 'finalizada'], true)) {
            return $session;
        }

        if ($session->disponible_hasta && now()->greaterThanOrEqualTo($session->disponible_hasta)) {
            $this->exams->submitPending($session);

            return $this->engine->cerrar($session->anfitrion()->firstOrFail(), $session->fresh());
        }

        if ($session->estado === 'espera'
            && $session->disponible_desde
            && now()->greaterThanOrEqualTo($session->disponible_desde)) {
            $session = $this->engine->iniciar($session->anfitrion()->firstOrFail(), $session);
        }

        if ($session->estado === 'activa') {
            foreach ($session->participantes()->where('es_anfitrion', false)->where('estado', '!=', 'finalizado')
                ->whereNotNull('examen_limite_at')->where('examen_limite_at', '<=', now())->with('usuario')->get() as $participant) {
                if ($participant->usuario) {
                    $this->exams->submit($participant->usuario, $session->fresh(), true);
                }
            }
            $session = $session->fresh();
        }

        if ($session->estado === 'activa' && $this->entregaronTodosLosAlumnos($session)) {
            $this->exams->submitPending($session);

            return $this->engine->cerrar($session->anfitrion()->firstOrFail(), $session->fresh());
        }

        return $session->fresh();
    }

    public function reviewUntil(TestSesion $session): ?Carbon
    {
        if (! $session->cerrada_at) {
            return null;
        }
        $minutes = max(5, (int) (($session->configuracion ?? [])['resumen_disponible_minutos'] ?? 60));

        return $session->cerrada_at->copy()->addMinutes($minutes);
    }

    public function canReview(Usuario $student, TestSesion $session): bool
    {
        if ($session->estado !== 'cerrada' || ! $session->cerrada_at) {
            return false;
        }
        $until = $this->reviewUntil($session);
        if (! $until || now()->greaterThanOrEqualTo($until)) {
            return false;
        }

        return $session->participantes()->where('usuario_id', $student->id)->where('estado', 'finalizado')->exists()
            && $session->resultados()->where('usuario_id', $student->id)->where('estado', 'entregado')->exists();
    }

    private function entregaronTodosLosAlumnos(TestSesion $session): bool
    {
        $studentIds = $session->clase()->firstOrFail()->alumnos()
            ->where('usuarios.activo', true)
            ->pluck('usuarios.id');
        if ($studentIds->isEmpty()) {
            return false;
        }

        $completed = $session->participantes()
            ->whereIn('usuario_id', $studentIds)
            ->where('estado', 'finalizado')
            ->distinct()
            ->count('usuario_id');

        return $completed === $studentIds->count();
    }
}
