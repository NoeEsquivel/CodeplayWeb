<?php

namespace App\Http\Controllers;

use App\Models\Clase;
use App\Models\Colegio;
use App\Models\Usuario;
use App\Services\AdminAuditService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ColegioController extends Controller
{
    public function __construct(private readonly AdminAuditService $audit) {}

    public function index(): JsonResponse
    {
        return response()->json(Colegio::query()
            ->withCount([
                'usuarios', 'clases',
                'usuarios as administradores_count' => fn ($q) => $q->where('rol', Usuario::ROL_ADMINISTRADOR_COLEGIO),
                'usuarios as profesores_count' => fn ($q) => $q->where('rol', Usuario::ROL_PROFESOR),
                'usuarios as alumnos_count' => fn ($q) => $q->where('rol', Usuario::ROL_ALUMNO),
            ])->orderBy('nombre')->get());
    }

    public function show(Colegio $colegio): JsonResponse
    {
        $columnas = ['id', 'colegio_id', 'nombre', 'nombre_completo', 'email', 'rol', 'ultimo_acceso'];
        if (Schema::hasColumn('usuarios', 'activo')) $columnas[] = 'activo';

        $colegio->load([
            'usuarios' => fn ($q) => $q->select($columnas)->orderBy('nombre'),
            'clases' => fn ($q) => $q->with([
                'profesores' => fn ($p) => $p->select('usuarios.id', 'usuarios.nombre', 'usuarios.nombre_completo', 'usuarios.email'),
                'alumnos' => fn ($a) => $a->select('usuarios.id', 'usuarios.nombre', 'usuarios.nombre_completo'),
            ])->withCount(['profesores', 'alumnos'])->orderBy('nombre'),
        ]);

        return response()->json([
            'colegio' => $colegio,
            'administradores' => $colegio->usuarios->where('rol', Usuario::ROL_ADMINISTRADOR_COLEGIO)->values(),
            'profesores' => $colegio->usuarios->where('rol', Usuario::ROL_PROFESOR)->values(),
            'alumnos' => $colegio->usuarios->where('rol', Usuario::ROL_ALUMNO)->values(),
            'clases' => $colegio->clases,
        ]);
    }

    public function printHierarchy(Request $request, Colegio $colegio): View
    {
        $this->actor($request);
        $columnas = ['id', 'colegio_id', 'nombre', 'nombre_completo', 'email', 'rol', 'ultimo_acceso'];
        if (Schema::hasColumn('usuarios', 'activo')) $columnas[] = 'activo';

        $colegio->load([
            'usuarios' => fn ($q) => $q->select($columnas)->orderByRaw('COALESCE(nombre_completo, nombre), nombre'),
            'clases' => fn ($q) => $q->with([
                'profesores' => fn ($p) => $p->select('usuarios.id', 'usuarios.nombre', 'usuarios.nombre_completo', 'usuarios.email'),
                'alumnos' => fn ($a) => $a->select('usuarios.id', 'usuarios.nombre', 'usuarios.nombre_completo'),
            ])->withCount(['profesores', 'alumnos'])->orderBy('nombre'),
        ]);

        return view('master.jerarquia-print', [
            'colegio' => $colegio,
            'administradores' => $colegio->usuarios->where('rol', Usuario::ROL_ADMINISTRADOR_COLEGIO)->values(),
            'profesores' => $colegio->usuarios->where('rol', Usuario::ROL_PROFESOR)->values(),
            'alumnos' => $colegio->usuarios->where('rol', Usuario::ROL_ALUMNO)->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $nombre = $this->normalizarNombre((string) $request->input('nombre'));
        $request->merge(['nombre' => $nombre]);
        $datos = $request->validate(['nombre' => ['required', 'string', 'max:150']], $this->mensajesValidacion());
        if ($this->nombreExiste($datos['nombre'])) return $this->respuestaNombreDuplicado();

        try {
            $colegio = DB::transaction(function () use ($datos) {
                $colegio = Colegio::create([
                    'nombre' => $datos['nombre'],
                    'codigo' => $this->generarCodigo($datos['nombre']),
                    'activo' => true,
                ]);
                Clase::create([
                    'colegio_id' => $colegio->id,
                    'nombre' => 'Clase General',
                    'codigo' => 'GENERAL',
                    'descripcion' => 'Clase predeterminada del colegio.',
                    'activa' => true,
                ]);
                return $colegio;
            });
        } catch (QueryException $exception) {
            if ($this->esConflictoUnico($exception)) return $this->respuestaNombreDuplicado();
            throw $exception;
        }

        $this->audit->record($request, $this->actor($request), 'colegio.creado', $colegio, [
            'nombre' => $colegio->nombre, 'codigo' => $colegio->codigo,
        ]);

        return response()->json([
            'success' => true, 'message' => 'Colegio creado exitosamente.', 'colegio' => $colegio,
        ], 201);
    }

    public function update(Request $request, Colegio $colegio): JsonResponse
    {
        if ($request->has('nombre')) {
            $request->merge(['nombre' => $this->normalizarNombre((string) $request->input('nombre'))]);
        }
        $datos = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:150', Rule::unique('colegios', 'nombre')->ignore($colegio->id)],
            'activo' => ['sometimes', 'boolean'],
        ], $this->mensajesValidacion());

        $anterior = $colegio->only(['nombre', 'activo']);
        DB::transaction(function () use ($colegio, $datos) {
            $colegio->fill($datos)->save();
            if (array_key_exists('activo', $datos) && !$datos['activo']
                && Schema::hasColumn('usuarios', 'session_version')) {
                Usuario::query()->where('colegio_id', $colegio->id)->increment('session_version');
            }
        });

        $accion = array_key_exists('activo', $datos) && (bool) $anterior['activo'] !== (bool) $datos['activo']
            ? ($datos['activo'] ? 'colegio.activado' : 'colegio.desactivado')
            : 'colegio.actualizado';
        $this->audit->record($request, $this->actor($request), $accion, $colegio, [
            'antes' => $anterior, 'despues' => $colegio->only(['nombre', 'activo']),
        ]);

        return response()->json([
            'success' => true, 'message' => 'Colegio actualizado exitosamente.', 'colegio' => $colegio->fresh(),
        ]);
    }

    public function destroy(Request $request, Colegio $colegio): JsonResponse
    {
        if ($colegio->usuarios()->exists()) {
            return response()->json([
                'success' => false, 'code' => 'COLLEGE_IN_USE',
                'message' => 'No se puede eliminar el colegio porque tiene usuarios asignados. Puedes desactivarlo.',
            ], 409);
        }

        $this->audit->record($request, $this->actor($request), 'colegio.eliminado', $colegio, [
            'nombre' => $colegio->nombre, 'codigo' => $colegio->codigo,
        ]);
        $colegio->delete();
        return response()->json(['success' => true, 'message' => 'Colegio eliminado exitosamente.']);
    }

    private function actor(Request $request): Usuario
    {
        $actor = $request->attributes->get('usuario_actual');
        abort_unless($actor instanceof Usuario && $actor->esMaster(), 403);
        return $actor;
    }

    private function generarCodigo(string $nombre): string
    {
        $base = strtoupper(Str::slug($nombre, '-'));
        $base = $base !== '' ? mb_substr($base, 0, 30, 'UTF-8') : 'COLEGIO';
        $codigo = $base;
        $numero = 2;
        while (Colegio::where('codigo', $codigo)->exists()) {
            $sufijo = '-'.$numero++;
            $codigo = rtrim(mb_substr($base, 0, 30 - strlen($sufijo), 'UTF-8'), '-').$sufijo;
        }
        return $codigo;
    }

    private function normalizarNombre(string $nombre): string
    {
        return preg_replace('/\s+/u', ' ', trim($nombre)) ?? trim($nombre);
    }

    private function nombreExiste(string $nombre): bool
    {
        return Colegio::query()->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre, 'UTF-8')])->exists();
    }

    private function respuestaNombreDuplicado(): JsonResponse
    {
        return response()->json([
            'success' => false, 'code' => 'COLLEGE_NAME_TAKEN',
            'message' => 'Ya existe un colegio registrado con ese nombre.',
            'errors' => ['nombre' => ['Ya existe un colegio registrado con ese nombre.']],
        ], 422);
    }

    private function esConflictoUnico(QueryException $exception): bool
    {
        return in_array((string) $exception->getCode(), ['23000', '23505'], true)
            || str_contains(strtolower($exception->getMessage()), 'unique');
    }

    private function mensajesValidacion(): array
    {
        return [
            'nombre.required' => 'El nombre del colegio es obligatorio.',
            'nombre.unique' => 'Ya existe un colegio registrado con ese nombre.',
            'nombre.max' => 'El nombre del colegio no puede superar los 150 caracteres.',
        ];
    }
}
