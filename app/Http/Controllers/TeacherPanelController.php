<?php

namespace App\Http\Controllers;

use App\Models\Clase;
use App\Models\Usuario;
use App\Services\ExcelExportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Http\Response;

class TeacherPanelController extends Controller
{
    private const DOCUMENTATION_TOTAL = 22;
    private const ADVENTURE_TOTAL = 50;
    private const MODULES = ['todos', 'documentacion', 'tutoriales', 'desafios', 'aventura'];

    public function classes(Request $request): JsonResponse
    {
        $teacher = $this->teacher($request);

        return response()->json($teacher->clasesComoProfesor()
            ->with(['alumnos' => fn ($q) => $q
                ->select('usuarios.id', 'usuarios.nombre', 'usuarios.nombre_completo', 'usuarios.puntos_totales')
                ->orderBy('usuarios.nombre')])
            ->withCount('alumnos')
            ->orderBy('clases.nombre')
            ->get());
    }

    public function report(Request $request): JsonResponse
    {
        [$teacher, $filters] = $this->validatedContext($request);
        $rows = $this->reportRows($teacher, $filters);

        return response()->json([
            'filtros' => $filters,
            'resumen' => $this->summary($rows),
            'alumnos' => $rows,
        ]);
    }

    public function export(Request $request, ExcelExportService $excel): Response
    {
        [$teacher, $filters] = $this->validatedContext($request);
        $reportRows = $this->reportRows($teacher, $filters);
        $rows = [];

        foreach ($reportRows as $row) {
            foreach ($row['modulos'] as $module => $progress) {
                $rows[] = [
                    $row['nombre'], implode(', ', $row['clases']), ucfirst($module),
                    (int) $progress['completados'], (int) $progress['total'],
                    (float) $progress['porcentaje'], $progress['detalle'],
                ];
            }
        }

        $contents = $excel->build([
            'Alumno', 'Clases', 'Módulo', 'Completados / avance', 'Total', 'Porcentaje', 'Detalle',
        ], $rows, 'Progreso de clases');

        return response($contents, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="progreso-clases-'.now()->format('Y-m-d-His').'.xlsx"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }
    public function assignStudent(Request $request, Clase $clase, Usuario $usuario): JsonResponse
    {
        $teacher = $this->teacher($request);
        $this->ensureOwnClass($teacher, $clase);
        abort_unless($usuario->esAlumno() && (int) $usuario->colegio_id === (int) $teacher->colegio_id, 422, 'El alumno no pertenece a tu colegio.');

        $alreadyVisible = $this->studentsQuery($teacher)->whereKey($usuario->id)->exists();
        abort_unless($alreadyVisible, 403, 'Solo puedes asignar alumnos ya vinculados a una de tus clases.');

        $clase->agregarAlumno($usuario);
        return response()->json(['success' => true, 'message' => 'Alumno asignado a la clase.']);
    }

    public function removeStudent(Request $request, Clase $clase, Usuario $usuario): JsonResponse
    {
        $teacher = $this->teacher($request);
        $this->ensureOwnClass($teacher, $clase);
        abort_unless($usuario->esAlumno() && $clase->alumnos()->whereKey($usuario->id)->exists(), 404);
        $clase->quitarAlumno($usuario);

        return response()->json(['success' => true, 'message' => 'Alumno retirado de la clase.']);
    }

    private function validatedContext(Request $request): array
    {
        $teacher = $this->teacher($request);
        $data = $request->validate([
            'clase_id' => ['nullable', 'integer', 'exists:clases,id'],
            'alumno_id' => ['nullable', 'integer', 'exists:usuarios,id'],
            'modulo' => ['nullable', Rule::in(self::MODULES)],
            'buscar' => ['nullable', 'string', 'max:160'],
        ]);
        $filters = [
            'clase_id' => isset($data['clase_id']) ? (int) $data['clase_id'] : null,
            'alumno_id' => isset($data['alumno_id']) ? (int) $data['alumno_id'] : null,
            'modulo' => $data['modulo'] ?? 'todos',
            'buscar' => trim((string) ($data['buscar'] ?? '')),
        ];

        if ($filters['clase_id']) {
            $class = Clase::findOrFail($filters['clase_id']);
            $this->ensureOwnClass($teacher, $class);
        }

        $students = $this->studentsQuery($teacher, $filters['clase_id']);
        if ($filters['alumno_id'] && !(clone $students)->whereKey($filters['alumno_id'])->exists()) {
            abort(403, 'No tienes acceso al progreso de este alumno.');
        }

        return [$teacher, $filters];
    }

    private function reportRows(Usuario $teacher, array $filters): array
    {
        $students = $this->studentsQuery($teacher, $filters['clase_id']);
        if ($filters['alumno_id']) $students->whereKey($filters['alumno_id']);
        if ($filters['buscar'] !== '') {
            $search = $filters['buscar'];
            $students->where(function (Builder $query) use ($search) {
                $query->where('nombre_completo', 'like', '%'.$search.'%')
                    ->orWhere('nombre', 'like', '%'.$search.'%');
            });
        }

        return $students->orderBy('nombre')->get([
            'id', 'nombre', 'nombre_completo', 'puntos_totales', 'puntos_usados', 'ultimo_acceso',
        ])->map(function (Usuario $student) use ($teacher, $filters) {
            $modules = $this->studentProgress($student);
            if ($filters['modulo'] !== 'todos') {
                $modules = [$filters['modulo'] => $modules[$filters['modulo']]];
            }

            return [
                'id' => $student->id,
                'nombre' => $student->nombreParaReportes(),
                'usuario' => $student->nombre,
                'puntos_totales' => (int) $student->puntos_totales,
                'ultimo_acceso' => $student->ultimo_acceso?->toIso8601String(),
                'clases' => $student->clasesComoAlumno()
                    ->whereHas('profesores', fn ($q) => $q->where('usuarios.id', $teacher->id))
                    ->orderBy('clases.nombre')->pluck('clases.nombre')->all(),
                'modulos' => $modules,
            ];
        })->all();
    }

    private function studentsQuery(Usuario $teacher, ?int $classId = null): Builder
    {
        $classIds = $teacher->clasesComoProfesor()->pluck('clases.id');

        return Usuario::query()
            ->where('rol', Usuario::ROL_ALUMNO)
            ->where('colegio_id', $teacher->colegio_id)
            ->whereHas('clasesComoAlumno', function ($q) use ($classIds, $classId) {
                $q->whereIn('clases.id', $classIds);
                if ($classId) $q->where('clases.id', $classId);
            });
    }

    private function studentProgress(Usuario $student): array
    {
        $documentation = Schema::hasTable('progreso_documentacion')
            ? DB::table('progreso_documentacion')->where('usuario_id', $student->id)->distinct()->count('documento') : 0;

        $tutorialTotal = Schema::hasTable('tutoriales') ? DB::table('tutoriales')->count() : 0;
        $tutorialDone = Schema::hasTable('progreso_tutorial')
            ? DB::table('progreso_tutorial')->where('usuario_id', $student->id)->where('estado', 'completado')->distinct()->count('tutorial_id') : 0;

        $challengeTotal = Schema::hasTable('niveles_desafio') ? DB::table('niveles_desafio')->count() : 20;
        $challengeDone = Schema::hasTable('desafio_recompensas')
            ? DB::table('desafio_recompensas')->where('usuario_id', $student->id)
                ->where('completado_primera_vez', true)->distinct()->count('nivel_desafio') : 0;

        $adventureLevel = Schema::hasTable('aventura_personajes')
            ? (int) (DB::table('aventura_personajes')->where('usuario_id', $student->id)->value('nivel') ?? 0) : 0;
        $adventureScene = Schema::hasTable('aventura_expediciones')
            && Schema::hasColumn('aventura_expediciones', 'escenario')
            ? (int) (DB::table('aventura_expediciones')->where('usuario_id', $student->id)->max('escenario') ?? 0) : 0;
        $victories = Schema::hasTable('aventura_expediciones')
            ? DB::table('aventura_expediciones')->where('usuario_id', $student->id)->where('estado', 'victoria')->count() : 0;
        if ($victories > 0) $adventureScene = self::ADVENTURE_TOTAL;

        return [
            'documentacion' => $this->progress($documentation, self::DOCUMENTATION_TOTAL, $documentation.' documentos consultados'),
            'tutoriales' => $this->progress($tutorialDone, $tutorialTotal, $tutorialDone.' tutoriales completados'),
            'desafios' => $this->progress($challengeDone, $challengeTotal, $challengeDone.' desafíos completados'),
            'aventura' => $this->progress($adventureScene, self::ADVENTURE_TOTAL, 'Nivel '.$adventureLevel.' · escenario '.$adventureScene.' · victorias '.$victories),
        ];
    }

    private function progress(int $completed, int $total, string $detail): array
    {
        $completed = max(0, min($completed, max($total, $completed)));
        return [
            'completados' => $completed,
            'total' => $total,
            'porcentaje' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
            'detalle' => $detail,
        ];
    }

    private function summary(array $rows): array
    {
        $summary = ['alumnos' => count($rows), 'promedios' => []];
        foreach (['documentacion', 'tutoriales', 'desafios', 'aventura'] as $module) {
            $values = [];
            foreach ($rows as $row) {
                if (isset($row['modulos'][$module])) $values[] = $row['modulos'][$module]['porcentaje'];
            }
            if ($values !== []) $summary['promedios'][$module] = round(array_sum($values) / count($values), 1);
        }
        return $summary;
    }

    private function teacher(Request $request): Usuario
    {
        $teacher = $request->attributes->get('usuario_actual');
        abort_unless($teacher instanceof Usuario && $teacher->esProfesor(), 403);
        abort_unless($teacher->colegio_id, 422, 'Tu cuenta no tiene un colegio asignado.');
        return $teacher;
    }

    private function ensureOwnClass(Usuario $teacher, Clase $class): void
    {
        abort_unless($class->profesores()->whereKey($teacher->id)->exists()
            && (int) $class->colegio_id === (int) $teacher->colegio_id, 404);
    }


}
