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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserManagementController extends Controller
{
    private const ROLES_GESTIONABLES = [
        Usuario::ROL_ALUMNO,
        Usuario::ROL_PROFESOR,
        Usuario::ROL_ADMINISTRADOR_COLEGIO,
    ];

    public function __construct(private readonly AdminAuditService $audit) {}

    public function indexApi(Request $request, string $rol): JsonResponse
    {
        $actor = $this->actor($request);
        if (!$this->puedeAdministrarRol($actor, $rol)) return $this->forbidden();

        $query = Usuario::query()->with('colegio:id,nombre,codigo,activo')->where('rol', $rol);
        if ($actor->esProfesor()) {
            if (!$actor->colegio_id) return response()->json([]);
            $clases = $actor->clasesComoProfesor()->pluck('clases.id');
            $query->where('colegio_id', $actor->colegio_id)
                ->whereHas('clasesComoAlumno', fn ($q) => $q->whereIn('clases.id', $clases));
        } elseif ($actor->esAdministradorColegio()) {
            if (!$actor->colegio_id) return response()->json([]);
            $query->where('colegio_id', $actor->colegio_id);
        } elseif ($request->filled('colegio_id')) {
            $colegioId = $request->validate(['colegio_id' => ['integer', 'exists:colegios,id']])['colegio_id'];
            $query->where('colegio_id', $colegioId);
        }

        $columnas = [
            'id', 'colegio_id', 'nombre', 'email', 'rol', 'cambio_clave_requerido',
            'puntos_totales', 'puntos_usados', 'ultimo_acceso', 'created_at', 'updated_at',
        ];
        if (Schema::hasColumn('usuarios', 'activo')) $columnas[] = 'activo';

        return response()->json($query->orderBy('nombre')->get($columnas));
    }

    public function storeApi(Request $request): JsonResponse
    {
        $actor = $this->actor($request);
        if ($actor->esProfesor() && $request->input('rol') !== Usuario::ROL_ALUMNO) {
            return $this->forbidden();
        }
        if ($request->filled('email')) {
            $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')), 'UTF-8')]);
        }

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'email' => [
                Rule::requiredIf(fn () => in_array((string) $request->input('rol'), [
                    Usuario::ROL_PROFESOR, Usuario::ROL_ADMINISTRADOR_COLEGIO,
                ], true)),
                'nullable', 'string', 'email', 'max:150', Rule::unique('usuarios', 'email'),
            ],
            'rol' => ['required', Rule::in(self::ROLES_GESTIONABLES)],
            'colegio_id' => [
                Rule::requiredIf(fn () => $actor->esMaster()),
                'nullable', 'integer', 'exists:colegios,id',
            ],
            'clase_id' => [
                Rule::requiredIf(fn () => $actor->esProfesor()),
                'nullable', 'integer', 'exists:clases,id',
            ],
            'clave' => ['required', 'string', Password::min(8)->letters()->mixedCase()->numbers()],
        ], $this->mensajesValidacion());

        if (!$this->puedeAdministrarRol($actor, $datos['rol'])) return $this->forbidden();

        $colegioId = $actor->esMaster() ? (int) $datos['colegio_id'] : (int) $actor->colegio_id;
        if ($colegioId <= 0) {
            return response()->json([
                'success' => false, 'message' => 'Tu cuenta no tiene un colegio asignado.', 'code' => 'COLLEGE_REQUIRED',
            ], 422);
        }

        $colegio = Colegio::findOrFail($colegioId);
        if (!$colegio->activo) {
            return response()->json([
                'success' => false, 'message' => 'No se pueden crear usuarios dentro de un colegio desactivado.',
                'code' => 'COLLEGE_INACTIVE',
            ], 422);
        }

        $claseAsignada = null;
        if ($actor->esProfesor()) {
            $claseAsignada = Clase::query()
                ->whereKey($datos['clase_id'] ?? 0)
                ->where('colegio_id', $colegioId)
                ->where('activa', true)
                ->whereHas('profesores', fn ($q) => $q->where('usuarios.id', $actor->id))
                ->first();
            if (!$claseAsignada) {
                return response()->json([
                    'success' => false,
                    'code' => 'CLASS_NOT_ALLOWED',
                    'message' => 'Solo puedes asignar alumnos a una de tus clases activas.',
                ], 422);
            }
        }

        $nombre = $this->normalizarNombre((string) $datos['nombre']);
        if ($this->nombreExiste($nombre)) return $this->respuestaNombreOcupado($nombre);

        try {
            $usuario = DB::transaction(function () use ($datos, $nombre, $colegioId, $claseAsignada) {
                $atributos = [
                    'colegio_id' => $colegioId, 'nombre' => $nombre, 'email' => $datos['email'] ?? null,
                    'clave' => Hash::make($datos['clave']), 'rol' => $datos['rol'],
                    'cambio_clave_requerido' => true, 'puntos_totales' => 0, 'puntos_usados' => 0,
                ];
                if (Schema::hasColumn('usuarios', 'activo')) $atributos['activo'] = true;
                $usuario = Usuario::create($atributos);
                if ($claseAsignada) $claseAsignada->agregarAlumno($usuario);
                else $this->asignarClaseGeneral($usuario, $colegioId);
                return $usuario;
            });
        } catch (QueryException $exception) {
            if ($this->esConflictoUnico($exception)) return $this->respuestaNombreOcupado($nombre);
            throw $exception;
        }

        $this->audit->record($request, $actor, 'usuario.creado', $usuario, [
            'nombre' => $usuario->nombre, 'rol' => $usuario->rol, 'colegio' => $colegio->nombre,
            'clase_id' => $claseAsignada?->id, 'clase' => $claseAsignada?->nombre,
        ]);

        return response()->json([
            'success' => true, 'message' => 'Usuario creado exitosamente',
            'usuario' => $usuario->load('colegio:id,nombre,codigo,activo'),
        ], 201);
    }

    public function resetPasswordApi(Request $request, int $id): JsonResponse
    {
        $actor = $this->actor($request);
        $usuario = Usuario::findOrFail($id);
        if (!$this->puedeAdministrarUsuario($actor, $usuario)) return $this->forbidden();

        $datos = $request->validate([
            'nueva_clave' => ['required', 'string', Password::min(8)->letters()->mixedCase()->numbers()],
        ], $this->mensajesValidacion());

        $cambios = ['clave' => Hash::make($datos['nueva_clave']), 'cambio_clave_requerido' => true];
        if (Schema::hasColumn('usuarios', 'session_version')) {
            $cambios['session_version'] = ((int) $usuario->session_version) + 1;
        }
        $usuario->forceFill($cambios)->save();

        $this->audit->record($request, $actor, 'usuario.clave_restaurada', $usuario, [
            'nombre' => $usuario->nombre, 'rol' => $usuario->rol,
        ]);

        return response()->json(['success' => true, 'message' => 'Contraseña reseteada exitosamente']);
    }

    public function updateStatusApi(Request $request, int $id): JsonResponse
    {
        $actor = $this->actor($request);
        $usuario = Usuario::findOrFail($id);
        if ($usuario->is($actor)) {
            return response()->json(['success' => false, 'message' => 'No puedes desactivar tu propia cuenta.'], 422);
        }
        if (!$this->puedeAdministrarUsuario($actor, $usuario)) return $this->forbidden();

        $datos = $request->validate(['activo' => ['required', 'boolean']]);
        $usuario->activo = (bool) $datos['activo'];
        if (Schema::hasColumn('usuarios', 'session_version')) {
            $usuario->session_version = ((int) $usuario->session_version) + 1;
        }
        $usuario->save();

        $accion = $usuario->activo ? 'usuario.activado' : 'usuario.desactivado';
        $this->audit->record($request, $actor, $accion, $usuario, [
            'nombre' => $usuario->nombre, 'rol' => $usuario->rol,
        ]);

        return response()->json([
            'success' => true,
            'message' => $usuario->activo ? 'Usuario activado exitosamente.' : 'Usuario desactivado exitosamente.',
            'usuario' => $usuario->fresh()->load('colegio:id,nombre,codigo,activo'),
        ]);
    }

    public function moveCollegeApi(Request $request, int $id): JsonResponse
    {
        $actor = $this->actor($request);
        abort_unless($actor->esMaster(), 403);
        $usuario = Usuario::with('colegio')->findOrFail($id);
        if ($usuario->esMaster() || $usuario->is($actor)) return $this->forbidden();

        $datos = $request->validate(['colegio_id' => ['required', 'integer', 'exists:colegios,id']]);
        $destino = Colegio::findOrFail($datos['colegio_id']);
        if (!$destino->activo) {
            return response()->json([
                'success' => false, 'code' => 'COLLEGE_INACTIVE',
                'message' => 'No se puede trasladar un usuario a un colegio desactivado.',
            ], 422);
        }

        $origen = $usuario->colegio;
        if ((int) $usuario->colegio_id === (int) $destino->id) {
            return response()->json(['success' => true, 'message' => 'El usuario ya pertenece a ese colegio.', 'usuario' => $usuario]);
        }

        DB::transaction(function () use ($usuario, $destino) {
            if ($usuario->esProfesor()) $usuario->clasesComoProfesor()->detach();
            if ($usuario->esAlumno()) $usuario->clasesComoAlumno()->detach();

            $usuario->colegio_id = $destino->id;
            if (Schema::hasColumn('usuarios', 'session_version')) {
                $usuario->session_version = ((int) $usuario->session_version) + 1;
            }
            $usuario->save();
            $this->asignarClaseGeneral($usuario, $destino->id);
        });

        $this->audit->record($request, $actor, 'usuario.trasladado', $usuario, [
            'nombre' => $usuario->nombre,
            'colegio_anterior_id' => $origen?->id, 'colegio_anterior' => $origen?->nombre,
            'colegio_nuevo_id' => $destino->id, 'colegio_nuevo' => $destino->nombre,
        ], $destino->id);

        return response()->json([
            'success' => true, 'message' => 'Usuario trasladado exitosamente.',
            'usuario' => $usuario->fresh()->load('colegio:id,nombre,codigo,activo'),
        ]);
    }

    public function destroyApi(Request $request, int $id): JsonResponse
    {
        $actor = $this->actor($request);
        $usuario = Usuario::findOrFail($id);
        if ($usuario->is($actor)) {
            return response()->json(['success' => false, 'message' => 'No puedes eliminar tu propio usuario'], 422);
        }
        if (!$this->puedeAdministrarUsuario($actor, $usuario)) return $this->forbidden();

        $detalles = ['nombre' => $usuario->nombre, 'rol' => $usuario->rol, 'colegio_id' => $usuario->colegio_id];
        $this->audit->record($request, $actor, 'usuario.eliminado', $usuario, $detalles);
        try {
            $usuario->delete();
        } catch (QueryException) {
            return response()->json([
                'success' => false, 'message' => 'No se puede eliminar el usuario porque conserva información relacionada.',
            ], 409);
        }

        return response()->json(['success' => true, 'message' => 'Usuario eliminado exitosamente']);
    }

    private function asignarClaseGeneral(Usuario $usuario, int $colegioId): void
    {
        $clase = Clase::query()->where('colegio_id', $colegioId)
            ->where('codigo', 'GENERAL')->where('activa', true)->first();
        if ($clase && $usuario->esAlumno()) $clase->agregarAlumno($usuario);
        elseif ($clase && $usuario->esProfesor()) $clase->agregarProfesor($usuario);
    }

    private function actor(Request $request): Usuario
    {
        $actor = $request->attributes->get('usuario_actual');
        abort_unless($actor instanceof Usuario, 401, 'Usuario no autenticado.');
        return $actor;
    }

    private function puedeAdministrarRol(Usuario $actor, string $rol): bool
    {
        if ($actor->esMaster()) return in_array($rol, self::ROLES_GESTIONABLES, true);
        if ($actor->esAdministradorColegio()) {
            return in_array($rol, [Usuario::ROL_PROFESOR, Usuario::ROL_ALUMNO], true);
        }
        return $actor->esProfesor() && $rol === Usuario::ROL_ALUMNO;
    }

    private function puedeAdministrarUsuario(Usuario $actor, Usuario $objetivo): bool
    {
        if ($objetivo->esMaster() || !$this->puedeAdministrarRol($actor, (string) $objetivo->rol)) return false;
        if ($actor->esMaster()) return true;
        if (!$actor->colegio_id || (int) $actor->colegio_id !== (int) $objetivo->colegio_id) return false;
        if ($actor->esProfesor()) {
            return $objetivo->clasesComoAlumno()
                ->whereHas('profesores', fn ($q) => $q->where('usuarios.id', $actor->id))
                ->exists();
        }
        return $actor->esAdministradorColegio();
    }

    private function normalizarNombre(string $nombre): string
    {
        return preg_replace('/\s+/u', ' ', trim($nombre)) ?? trim($nombre);
    }

    private function nombreExiste(string $nombre): bool
    {
        return Usuario::query()->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre, 'UTF-8')])->exists();
    }

    private function sugerirNombre(string $nombre): string
    {
        for ($numero = 2; $numero <= 9999; $numero++) {
            $sufijo = ' '.$numero;
            $base = mb_substr($nombre, 0, 100 - mb_strlen($sufijo, 'UTF-8'), 'UTF-8');
            $sugerencia = rtrim($base).$sufijo;
            if (!$this->nombreExiste($sugerencia)) return $sugerencia;
        }
        return mb_substr($nombre, 0, 91, 'UTF-8').' '.substr((string) hrtime(true), -8);
    }

    private function respuestaNombreOcupado(string $nombre): JsonResponse
    {
        $sugerencia = $this->sugerirNombre($nombre);
        return response()->json([
            'success' => false, 'code' => 'USERNAME_TAKEN',
            'message' => "Ya existe un nombre de usuario con el nombre «{$nombre}». Te recomendamos usar «{$sugerencia}».",
            'suggested_username' => $sugerencia,
            'errors' => ['nombre' => ["El nombre de usuario ya existe. Puedes usar «{$sugerencia}»."]],
        ], 422);
    }

    private function esConflictoUnico(QueryException $exception): bool
    {
        return in_array((string) $exception->getCode(), ['23000', '23505'], true)
            || str_contains(strtolower($exception->getMessage()), 'unique');
    }

    private function forbidden(): JsonResponse
    {
        return response()->json(['success' => false, 'message' => 'No tienes permiso para administrar este usuario.'], 403);
    }

    private function mensajesValidacion(): array
    {
        return [
            'nombre.max' => 'El nombre no puede superar los 100 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio para profesores y administradores de colegio.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.unique' => 'Ya existe una cuenta registrada con este correo electrónico.',
            'email.max' => 'El correo no puede superar los 150 caracteres.',
            'rol.in' => 'El rol solicitado no está permitido.',
            'colegio_id.required' => 'Debes seleccionar un colegio.',
            'colegio_id.exists' => 'El colegio seleccionado no existe.',
            'clase_id.required' => 'Debes seleccionar una de tus clases.',
            'clase_id.exists' => 'La clase seleccionada no existe.',
            'clave.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'nueva_clave.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ];
    }
}
