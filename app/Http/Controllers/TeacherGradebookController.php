<?php

namespace App\Http\Controllers;

use App\Models\TestBanco;
use App\Models\TestHistorialRespuesta;
use App\Models\TestResultadoSesion;
use App\Models\TestSesion;
use App\Models\Usuario;
use App\Services\ExcelExportService;
use App\Services\GradeReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class TeacherGradebookController extends Controller
{
    public function index(Request $request, GradeReportService $reports): View
    {
        $teacher = $this->teacher();
        $filters = $this->filters($request);
        $results = $reports->queryForTeacher($teacher, $filters)->limit(1000)->get();
        $summary = $reports->summary($results, $filters['dimension']);
        $classes = $teacher->clasesComoProfesor()->orderBy('nombre')->get();
        $students = Usuario::whereHas('clasesComoAlumno', fn ($q) => $q->whereIn('clases.id', $classes->pluck('id')))->orderBy('nombre')->get();
        $sessions = TestSesion::where('anfitrion_usuario_id', $teacher->id)->where('tipo', 'clase')->whereNull('eliminada_at')->latest()->get();
        $banks = TestBanco::whereHas('sesiones', fn ($q) => $q->where('anfitrion_usuario_id', $teacher->id))->orderBy('nombre')->get();

        return view('profesor.calificaciones-index', compact('results', 'summary', 'filters', 'classes', 'students', 'sessions', 'banks'));
    }

    public function detail(TestResultadoSesion $resultado): View
    {
        $teacher = $this->teacher();
        $this->ownResult($teacher, $resultado);
        $resultado->load(['sesion.clase', 'sesion.colegio', 'sesion.anfitrion', 'sesion.bancos', 'usuario']);
        $answers = TestHistorialRespuesta::where('sesion_id', $resultado->sesion_id)->where('participante_id', $resultado->participante_id)->where('intento', $resultado->intento)->orderBy('ronda')->orderBy('orden')->get();

        return view('profesor.calificaciones-individual', compact('resultado', 'answers'));
    }

    public function printClass(Request $request, GradeReportService $reports): View
    {
        $teacher = $this->teacher();
        $filters = $this->filters($request);
        abort_unless(! empty($filters['clase_id']), 422);
        $this->ownClassId($teacher, (int) $filters['clase_id']);
        $class = $teacher->clasesComoProfesor()->findOrFail((int) $filters['clase_id']);
        $results = $reports->queryForTeacher($teacher, $filters)->get();

        return view('profesor.calificaciones-clase-print', compact('results', 'filters', 'class', 'teacher'));
    }

    public function export(Request $request, GradeReportService $reports, ExcelExportService $excel): Response
    {
        $teacher = $this->teacher();
        $results = $reports->queryForTeacher($teacher, $this->filters($request))->get();
        $rows = $results->map(fn (TestResultadoSesion $result) => [
            $result->nombreAlumnoReporte(), $result->clase?->nombre,
            $result->sesion->configuracion['titulo'] ?? $result->sesion->codigo_sala,
            $result->sesion->bancos->pluck('nombre')->join(' + '),
            $result->sesion->anfitrion?->nombreParaReportes(), $result->sesion->colegio?->nombre,
            ucfirst($result->modalidad), (int) $result->intento, (float) $result->calificacion,
            (int) $result->correctas, (int) $result->incorrectas, (int) $result->puntos_test,
            $result->duracion_segundos === null ? null : (int) $result->duracion_segundos,
            optional($result->entregada_at)->format('d/m/Y H:i'),
        ]);
        $contents = $excel->build([
            'Alumno', 'Clase', 'Actividad', 'Banco', 'Profesor', 'Colegio', 'Modalidad', 'Intento',
            'Calificación', 'Correctas', 'Incorrectas', 'Puntos de Test', 'Duración (segundos)', 'Fecha',
        ], $rows, 'Calificaciones');

        return response($contents, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="calificaciones-codeplay-'.now()->format('Y-m-d-His').'.xlsx"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    private function filters(Request $r): array
    {
        return $r->validate(['dimension' => ['nullable', 'in:alumno,clase,sesion,banco,profesor,colegio'], 'alumno_id' => ['nullable', 'integer'], 'clase_id' => ['nullable', 'integer'], 'sesion_id' => ['nullable', 'integer'], 'banco_id' => ['nullable', 'integer'], 'modalidad' => ['nullable', 'in:trivia,examen'], 'desde' => ['nullable', 'date'], 'hasta' => ['nullable', 'date', 'after_or_equal:desde']]) + ['dimension' => $r->input('dimension', 'clase')];
    }

    private function teacher(): Usuario
    {
        $u = Usuario::findOrFail((int) session('usuario_id'));
        abort_unless($u->esProfesor(), 403);

        return $u;
    }

    private function ownClassId(Usuario $t, int $id): void
    {
        abort_unless($t->clasesComoProfesor()->whereKey($id)->exists(), 404);
    }

    private function ownSession(Usuario $t, TestSesion $s): void
    {
        abort_unless((int) $s->anfitrion_usuario_id === (int) $t->id && (int) $s->colegio_id === (int) $t->colegio_id && $t->clasesComoProfesor()->whereKey($s->clase_id)->exists(), 404);
    }

    private function ownResult(Usuario $t, TestResultadoSesion $r): void
    {
        $this->ownSession($t, $r->sesion);
    }
}
