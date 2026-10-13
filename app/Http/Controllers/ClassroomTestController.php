<?php

namespace App\Http\Controllers;

use App\Models\TestHistorialRespuesta;
use App\Models\TestSesion;
use App\Models\TestSesionPregunta;
use App\Models\Usuario;
use App\Services\ClassroomExamLifecycleService;
use App\Services\ClassroomExamService;
use App\Services\LiveTestEngineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassroomTestController extends Controller
{
    public function index(ClassroomExamLifecycleService $lifecycle): View
    {
        $student = $this->student();
        $classIds = $student->clasesComoAlumno()->pluck('clases.id');
        $candidates = TestSesion::where('tipo', 'clase')->where('colegio_id', $student->colegio_id)->whereIn('clase_id', $classIds)
            ->where(function ($query) use ($student) {
                $query->whereIn('estado', ['espera', 'activa'])
                    ->orWhere(function ($closed) use ($student) {
                        $closed->where('estado', 'cerrada')->where('modalidad', 'examen')->where('cerrada_at', '>', now()->subDays(30))
                            ->whereHas('participantes', fn ($participants) => $participants->where('usuario_id', $student->id));
                    });
            })->latest()->get();
        foreach ($candidates->whereIn('estado', ['espera', 'activa'])->where('modalidad', 'examen') as $candidate) {
            $lifecycle->synchronize($candidate);
        }
        $sessions = TestSesion::whereIn('id', $candidates->pluck('id'))
            ->with(['clase:id,nombre', 'anfitrion:id,nombre', 'bancos:id,nombre'])
            ->withExists(['participantes as ya_ingreso' => fn ($q) => $q->where('usuario_id', $student->id)])->latest()->get()
            ->filter(function (TestSesion $session) use ($student, $lifecycle) {
                if ($session->modalidad === 'examen' && $lifecycle->canReview($student, $session)) {
                    return true;
                }

                return in_array($session->estado, ['espera', 'activa'], true)
                    && (! $session->disponible_desde || now()->greaterThanOrEqualTo($session->disponible_desde))
                    && (! $session->disponible_hasta || now()->lessThan($session->disponible_hasta));
            })->values();

        return view('alumno.tests-clase', compact('sessions'));
    }

    public function enter(Request $request, TestSesion $sesion, LiveTestEngineService $engine, ClassroomExamLifecycleService $lifecycle): RedirectResponse
    {
        if ($sesion->modalidad === 'examen') {
            $sesion = $lifecycle->synchronize($sesion);
        }
        $student = $this->student();
        $this->visible($student, $sesion);
        abort_unless(in_array($sesion->estado, ['espera', 'activa'], true)
            && (! $sesion->disponible_desde || now()->greaterThanOrEqualTo($sesion->disponible_desde))
            && (! $sesion->disponible_hasta || now()->lessThan($sesion->disponible_hasta)), 404);
        try {
            $participant = $sesion->participantes()->where('usuario_id', $student->id)->first();
            if (! $participant) {
                $joined = $engine->unir($student, $sesion->codigo_sala, null, 'clase');
                $request->session()->put("live_test_tokens.{$sesion->id}", $joined['reconexion_token']);
            }

            return redirect()->route($sesion->modalidad === 'examen' ? 'alumno.tests.clase.examen' : 'alumno.tests.clase.trivia', $sesion);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function trivia(TestSesion $sesion, LiveTestEngineService $engine): View
    {
        abort_unless($sesion->modalidad === 'trivia', 404);
        $this->visible($this->student(), $sesion);
        abort_unless($sesion->participantes()->where('usuario_id', $this->student()->id)->exists(), 404);
        try {
            $state = $engine->estado($this->student(), $sesion);
        } catch (\DomainException) {
            abort(404);
        }

        return view('alumno.tests-clase-trivia', ['sesion' => $sesion, 'estado' => $state, 'token' => session("live_test_tokens.{$sesion->id}")]);
    }

    public function exam(Request $request, TestSesion $sesion, ClassroomExamService $exams, ClassroomExamLifecycleService $lifecycle): View
    {
        $sesion = $lifecycle->synchronize($sesion);
        abort_unless($sesion->modalidad === 'examen', 404);
        $this->visible($this->student(), $sesion);
        abort_unless($sesion->participantes()->where('usuario_id', $this->student()->id)->exists(), 404);
        if ($sesion->estado === 'cerrada') {
            abort_unless($lifecycle->canReview($this->student(), $sesion), 404);
        }
        try {
            $state = $exams->estado($this->student(), $sesion, $request->integer('pregunta') ?: null);
        } catch (\DomainException) {
            abort(404);
        }
        $sesion = $lifecycle->synchronize($sesion)->loadMissing(['clase', 'anfitrion']);
        $puedeRevisar = $lifecycle->canReview($this->student(), $sesion);
        $resumenHasta = $lifecycle->reviewUntil($sesion);

        return view('alumno.tests-clase-examen', compact('sesion', 'state', 'puedeRevisar', 'resumenHasta'));
    }

    public function saveExam(Request $request, TestSesion $sesion, TestSesionPregunta $emision, ClassroomExamService $exams, ClassroomExamLifecycleService $lifecycle): RedirectResponse
    {
        $sesion = $lifecycle->synchronize($sesion);
        abort_unless($sesion->modalidad === 'examen', 404);
        $this->visible($this->student(), $sesion);
        $data = $request->validate(['respuesta' => ['present'], 'destino' => ['nullable', 'integer']]);
        try {
            $exams->save($this->student(), $sesion, $emision, $data['respuesta']);
            $parameters = ['sesion' => $sesion];
            if (! empty($data['destino'])) {
                $parameters['pregunta'] = (int) $data['destino'];
            }

            return redirect()->route('alumno.tests.clase.examen', $parameters)->with('success', 'Respuesta guardada. Puedes cambiarla mientras no entregues el examen.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function submitExam(TestSesion $sesion, ClassroomExamService $exams, ClassroomExamLifecycleService $lifecycle): RedirectResponse
    {
        $sesion = $lifecycle->synchronize($sesion);
        abort_unless($sesion->modalidad === 'examen', 404);
        $this->visible($this->student(), $sesion);
        try {
            $exams->submit($this->student(), $sesion);
            $lifecycle->synchronize($sesion->fresh());

            return back()->with('success', '¡Examen entregado correctamente!')->with('exam_submitted', true);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function summary(TestSesion $sesion, ClassroomExamLifecycleService $lifecycle): View
    {
        $student = $this->student();
        $this->visible($student, $sesion);
        $sesion = $lifecycle->synchronize($sesion);
        abort_unless($sesion->modalidad === 'examen' && $lifecycle->canReview($student, $sesion), 404);
        $participant = $sesion->participantes()->where('usuario_id', $student->id)->firstOrFail();
        $result = $sesion->resultados()->where('participante_id', $participant->id)->latest('intento')->firstOrFail();
        $answers = TestHistorialRespuesta::where('sesion_id', $sesion->id)->where('participante_id', $participant->id)
            ->where('intento', $result->intento)->orderBy('ronda')->orderBy('orden')->get();
        $result->forceFill(['revisada_at' => now()])->save();
        $sesion->loadMissing(['clase', 'anfitrion']);
        $resumenHasta = $lifecycle->reviewUntil($sesion);

        return view('alumno.tests-clase-examen-resumen', compact('sesion', 'result', 'answers', 'resumenHasta'));
    }

    private function visible(Usuario $student, TestSesion $session): void
    {
        abort_unless($session->tipo === 'clase' && (int) $session->colegio_id === (int) $student->colegio_id
            && $student->clasesComoAlumno()->whereKey($session->clase_id)->exists(), 404);
    }

    private function student(): Usuario
    {
        $u = Usuario::findOrFail((int) session('usuario_id'));
        abort_unless($u->esAlumno(), 403);

        return $u;
    }
}
