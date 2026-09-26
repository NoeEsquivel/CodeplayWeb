<?php

namespace App\Http\Controllers;

use App\Models\Clase;
use App\Models\Usuario;
use App\Services\AdminAuditService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SchoolAdminController extends Controller
{
    public function __construct(private readonly AdminAuditService $audit) {}

    public function summary(Request $request): JsonResponse
    {
        $actor = $this->actor($request);
        $colegioId = $this->collegeId($actor);

        $usuarios = Usuario::query()->where('colegio_id', $colegioId);
        $alumnos = (clone $usuarios)->where('rol', Usuario::ROL_ALUMNO);
        $profesores = (clone $usuarios)->where('rol', Usuario::ROL_PROFESOR);

        $resumen = [
            'colegio' => $actor->colegio()->select('id', 'nombre', 'codigo', 'activo')->firstOrFail(),
            'usuarios' => [
                'administradores' => (clone $usuarios)->where('rol', Usuario::ROL_ADMINISTRADOR_COLEGIO)->count(),
                'profesores' => (clone $profesores)->count(),
                'alumnos' => (clone $alumnos)->count(),
                'activos' => Schema::hasColumn('usuarios', 'activo') ? (clone $usuarios)->where('activo', true)->count() : (clone $usuarios)->count(),
                'desactivados' => Schema::hasColumn('usuarios', 'activo') ? (clone $usuarios)->where('activo', false)->count() : 0,
            ],
            'clases' => [
                'total' => Clase::where('colegio_id', $colegioId)->count(),
                'activas' => Clase::where('colegio_id', $colegioId)->where('activa', true)->count(),
            ],
            'progreso' => $this->progressStats($colegioId),
            'alumnos_destacados' => (clone $alumnos)
                ->orderByDesc('puntos_totales')->orderBy('nombre')
                ->limit(5)->get(['id', 'nombre', 'puntos_totales', 'puntos_usados']),
        ];

        return response()->json($resumen);
    }

    public function classes(Request $request): JsonResponse
    {
        $actor = $this->actor($request);
        $colegioId = $this->collegeId($actor);

        return response()->json(Clase::query()
            ->where('colegio_id', $colegioId)
            ->with([
                'profesores' => fn ($q) => $q->select('usuarios.id', 'usuarios.nombre', 'usuarios.email')->orderBy('usuarios.nombre'),
                'alumnos' => fn ($q) => $q->select('usuarios.id', 'usuarios.nombre')->orderBy('usuarios.nombre'),
            ])
            ->withCount(['profesores', 'alumnos'])
            ->orderBy('nombre')
            ->get());
    }

    public function storeClass(Request $request): JsonResponse
    {
        $actor = $this->actor($request);
        $colegioId = $this->collegeId($actor);
        $request->merge(['nombre' => $this->normalize((string) $request->input('nombre'))]);

        $datos = $request->validate([
            'nombre' => [
                'required', 'string', 'max:120',
                Rule::unique('clases', 'nombre')->where(fn ($q) => $q->where('colegio_id', $colegioId)),
            ],
            'codigo' => ['nullable', 'string', 'max:50'],
            'ciclo_escolar' => ['nullable', 'string', 'max:20'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
        ], $this->messages());

        $codigo = $this->classCode($colegioId, $datos['codigo'] ?? $datos['nombre']);
        try {
            $clase = Clase::create([
                'colegio_id' => $colegioId,
                'nombre' => $datos['nombre'],
                'codigo' => $codigo,
                'ciclo_escolar' => $datos['ciclo_escolar'] ?? (string) now()->year,
                'descripcion' => $datos['descripcion'] ?? null,
                'activa' => true,
            ]);
        } catch (QueryException $exception) {
            if ($this->uniqueConflict($exception)) {
                return response()->json([
                    'success' => false, 'code' => 'CLASS_TAKEN',
                    'message' => 'Ya existe una clase con ese nombre o código dentro de tu colegio.',
                ], 422);
            }
            throw $exception;
        }

        $this->audit->record($request, $actor, 'clase.creada', $clase, [
            'nombre' => $clase->nombre, 'codigo' => $clase->codigo, 'ciclo_escolar' => $clase->ciclo_escolar,
        ], $colegioId);

        return response()->json(['success' => true, 'message' => 'Clase creada exitosamente.', 'clase' => $clase], 201);
    }

    public function updateClass(Request $request, Clase $clase): JsonResponse
    {
        $actor = $this->actor($request);
        $this->ensureClass($actor, $clase);
        if ($request->has('nombre')) $request->merge(['nombre' => $this->normalize((string) $request->input('nombre'))]);

        $datos = $request->validate([
            'nombre' => [
                'sometimes', 'required', 'string', 'max:120',
                Rule::unique('clases', 'nombre')->where(fn ($q) => $q->where('colegio_id', $actor->colegio_id))->ignore($clase->id),
            ],
            'ciclo_escolar' => ['sometimes', 'required', 'string', 'max:20'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'activa' => ['sometimes', 'boolean'],
        ], $this->messages());

        $antes = $clase->only(['nombre', 'ciclo_escolar', 'descripcion', 'activa']);
        $clase->fill($datos)->save();
        $this->audit->record($request, $actor, 'clase.actualizada', $clase, [
            'antes' => $antes, 'despues' => $clase->only(['nombre', 'ciclo_escolar', 'descripcion', 'activa']),
        ], (int) $actor->colegio_id);

        return response()->json(['success' => true, 'message' => 'Clase actualizada exitosamente.', 'clase' => $clase->fresh()]);
    }

    public function assignProfessor(Request $request, Clase $clase, Usuario $usuario): JsonResponse
    {
        return $this->assign($request, $clase, $usuario, Usuario::ROL_PROFESOR, true);
    }

    public function removeProfessor(Request $request, Clase $clase, Usuario $usuario): JsonResponse
    {
        return $this->assign($request, $clase, $usuario, Usuario::ROL_PROFESOR, false);
    }

    public function assignStudent(Request $request, Clase $clase, Usuario $usuario): JsonResponse
    {
        return $this->assign($request, $clase, $usuario, Usuario::ROL_ALUMNO, true);
    }

    public function removeStudent(Request $request, Clase $clase, Usuario $usuario): JsonResponse
    {
        return $this->assign($request, $clase, $usuario, Usuario::ROL_ALUMNO, false);
    }

    private function assign(Request $request, Clase $clase, Usuario $usuario, string $role, bool $attach): JsonResponse
    {
        $actor = $this->actor($request);
        $this->ensureClass($actor, $clase);
        abort_unless((int) $usuario->colegio_id === (int) $actor->colegio_id && $usuario->rol === $role, 422, 'El usuario seleccionado no pertenece a tu colegio o no tiene el rol requerido.');

        if ($role === Usuario::ROL_PROFESOR) {
            $attach ? $clase->agregarProfesor($usuario) : $clase->quitarProfesor($usuario);
        } else {
            $attach ? $clase->agregarAlumno($usuario) : $clase->quitarAlumno($usuario);
        }

        $accion = 'clase.'.($attach ? 'asignacion_creada' : 'asignacion_eliminada');
        $this->audit->record($request, $actor, $accion, $usuario, [
            'clase_id' => $clase->id, 'clase' => $clase->nombre, 'rol_asignado' => $role,
        ]);

        return response()->json([
            'success' => true,
            'message' => $attach ? 'Usuario asignado a la clase.' : 'Usuario retirado de la clase.',
        ]);
    }

    private function progressStats(int $colegioId): array
    {
        $studentIds = Usuario::query()->where('colegio_id', $colegioId)
            ->where('rol', Usuario::ROL_ALUMNO)->select('id');

        return [
            'puntos_totales' => (int) Usuario::query()->where('colegio_id', $colegioId)
                ->where('rol', Usuario::ROL_ALUMNO)->sum('puntos_totales'),
            'tutoriales_completados' => Schema::hasTable('progreso_tutorial')
                ? DB::table('progreso_tutorial')->whereIn('usuario_id', clone $studentIds)->where('estado', 'completado')->count() : 0,
            'desafios_completados' => Schema::hasTable('progreso_niveles')
                ? DB::table('progreso_niveles')->whereIn('usuario_id', clone $studentIds)->where('estado', 'completado')->count() : 0,
            'tests_realizados' => Schema::hasTable('resultados_test')
                ? DB::table('resultados_test')->whereIn('usuario_id', clone $studentIds)->count() : 0,
            'promedio_tests' => Schema::hasTable('resultados_test')
                ? round((float) (DB::table('resultados_test')->whereIn('usuario_id', clone $studentIds)->avg('puntaje') ?? 0), 2) : 0,
        ];
    }

    private function actor(Request $request): Usuario
    {
        $actor = $request->attributes->get('usuario_actual');
        abort_unless($actor instanceof Usuario && ($actor->esAdministradorColegio() || $actor->esMaster()), 403);
        return $actor;
    }

    private function collegeId(Usuario $actor): int
    {
        abort_unless($actor->colegio_id, 422, 'Tu cuenta no tiene un colegio asignado.');
        return (int) $actor->colegio_id;
    }

    private function ensureClass(Usuario $actor, Clase $clase): void
    {
        abort_unless((int) $clase->colegio_id === $this->collegeId($actor), 404);
    }

    private function normalize(string $value): string
    {
        return preg_replace('/\s+/u', ' ', trim($value)) ?? trim($value);
    }

    private function classCode(int $colegioId, string $value): string
    {
        $base = strtoupper(Str::slug($value, '-'));
        $base = $base !== '' ? mb_substr($base, 0, 42, 'UTF-8') : 'CLASE';
        $code = $base;
        $number = 2;
        while (Clase::where('colegio_id', $colegioId)->where('codigo', $code)->exists()) {
            $suffix = '-'.$number++;
            $code = rtrim(mb_substr($base, 0, 50 - strlen($suffix), 'UTF-8'), '-').$suffix;
        }
        return $code;
    }

    private function uniqueConflict(QueryException $exception): bool
    {
        return in_array((string) $exception->getCode(), ['23000', '23505'], true)
            || str_contains(strtolower($exception->getMessage()), 'unique');
    }

    private function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la clase es obligatorio.',
            'nombre.unique' => 'Ya existe una clase con ese nombre dentro de tu colegio.',
            'nombre.max' => 'El nombre no puede superar los 120 caracteres.',
            'ciclo_escolar.required' => 'El ciclo escolar es obligatorio.',
        ];
    }
}
