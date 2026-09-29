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
use Illuminate\Support\Str;
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
        if ($rol === Usuario::ROL_PROFESOR) {
            $query->with('clasesComoProfesor:id,nombre,ciclo_escolar,activa');
        } elseif ($rol === Usuario::ROL_ALUMNO) {
            $query->with('clasesComoAlumno:id,nombre,ciclo_escolar,activa');
        }
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
            'id', 'colegio_id', 'nombre', 'nombre_completo', 'email', 'rol', 'cambio_clave_requerido',
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
            'nombre_completo' => ['nullable', 'string', 'min:3', 'max:160'],
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
            'profesor_id' => ['nullable', 'integer', 'exists:usuarios,id'],
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
        } elseif (!empty($datos['clase_id'])) {
            $claseAsignada = Clase::query()
                ->whereKey($datos['clase_id'])
                ->where('colegio_id', $colegioId)
                ->where('activa', true)
                ->first();
            if (!$claseAsignada) {
                return response()->json([
                    'success' => false,
                    'code' => 'CLASS_NOT_ALLOWED',
                    'message' => 'La clase seleccionada no está activa o no pertenece a tu colegio.',
                ], 422);
            }
        }

        $profesorAsignado = null;
        if (!empty($datos['profesor_id'])) {
            if ($datos['rol'] !== Usuario::ROL_ALUMNO || !$claseAsignada) {
                return response()->json([
                    'success' => false,
                    'code' => 'TEACHER_CLASS_REQUIRED',
                    'message' => 'Para asignar un profesor debes seleccionar también una clase para el alumno.',
                ], 422);
            }
            $profesorAsignado = Usuario::query()
                ->whereKey($datos['profesor_id'])
                ->where('colegio_id', $colegioId)
                ->where('rol', Usuario::ROL_PROFESOR)
                ->when(Schema::hasColumn('usuarios', 'activo'), fn ($query) => $query->where('activo', true))
                ->first();
            if (!$profesorAsignado) {
                return response()->json([
                    'success' => false,
                    'code' => 'TEACHER_NOT_ALLOWED',
                    'message' => 'El profesor seleccionado no está activo o no pertenece a tu colegio.',
                ], 422);
            }
        }

        $nombreCompleto = $this->normalizarNombre((string) ($datos['nombre_completo'] ?? $datos['nombre']));
        $nombre = $this->normalizarNombre((string) $datos['nombre']);
        if ($this->nombreExiste($nombre)) return $this->respuestaNombreOcupado($nombre, $nombreCompleto);
        $asignarClaseGeneral = !($actor->esAdministradorColegio()
            && $datos['rol'] === Usuario::ROL_PROFESOR
            && !$claseAsignada);

        try {
            $usuario = DB::transaction(function () use ($datos, $nombre, $nombreCompleto, $colegioId, $claseAsignada, $profesorAsignado, $asignarClaseGeneral) {
                $atributos = [
                    'colegio_id' => $colegioId, 'nombre' => $nombre, 'nombre_completo' => $nombreCompleto, 'email' => $datos['email'] ?? null,
                    'clave' => Hash::make($datos['clave']), 'rol' => $datos['rol'],
                    'cambio_clave_requerido' => true, 'puntos_totales' => 0, 'puntos_usados' => 0,
                ];
                if (Schema::hasColumn('usuarios', 'activo')) $atributos['activo'] = true;
                $usuario = Usuario::create($atributos);
                if ($claseAsignada && $usuario->esProfesor()) $claseAsignada->agregarProfesor($usuario);
                elseif ($claseAsignada && $usuario->esAlumno()) $claseAsignada->agregarAlumno($usuario);
                elseif ($asignarClaseGeneral) $this->asignarClaseGeneral($usuario, $colegioId);
                if ($claseAsignada && $profesorAsignado) $claseAsignada->agregarProfesor($profesorAsignado);
                return $usuario;
            });
        } catch (QueryException $exception) {
            if ($this->esConflictoUnico($exception)) return $this->respuestaNombreOcupado($nombre, $nombreCompleto);
            throw $exception;
        }

        $this->audit->record($request, $actor, 'usuario.creado', $usuario, [
            'nombre' => $usuario->nombre, 'nombre_completo' => $usuario->nombre_completo, 'rol' => $usuario->rol, 'colegio' => $colegio->nombre,
            'clase_id' => $claseAsignada?->id, 'clase' => $claseAsignada?->nombre,
            'profesor_id' => $profesorAsignado?->id, 'profesor' => $profesorAsignado?->nombreParaReportes(),
        ]);

        return response()->json([
            'success' => true,
            'message' => $usuario->esProfesor() ? 'Profesor creado exitosamente.' : 'Alumno creado exitosamente.',
            'usuario' => $usuario->load('colegio:id,nombre,codigo,activo'),
        ], 201);
    }

    public function updateApi(Request $request, int $id): JsonResponse
    {
        $actor = $this->actor($request);
        $usuario = Usuario::findOrFail($id);
        if (!$this->puedeAdministrarUsuario($actor, $usuario)) return $this->forbidden();

        if ($request->filled('email')) {
            $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')), 'UTF-8')]);
        }
        $request->merge([
            'nombre_completo' => $this->normalizarNombre((string) $request->input('nombre_completo')),
            'nombre' => $this->normalizarNombre((string) $request->input('nombre')),
        ]);

        $datos = $request->validate([
            'nombre_completo' => ['required', 'string', 'min:3', 'max:160'],
            'nombre' => ['required', 'string', 'max:100'],
            'email' => [
                Rule::requiredIf(fn () => in_array($usuario->rol, [
                    Usuario::ROL_PROFESOR, Usuario::ROL_ADMINISTRADOR_COLEGIO,
                ], true)),
                'nullable', 'string', 'email', 'max:150', Rule::unique('usuarios', 'email')->ignore($usuario->id),
            ],
        ], $this->mensajesValidacion());

        if ($this->nombreExiste($datos['nombre'], $usuario->id)) {
            return $this->respuestaNombreOcupado($datos['nombre'], $datos['nombre_completo']);
        }

        $antes = $usuario->only(['nombre', 'nombre_completo', 'email']);
        try {
            $usuario->forceFill([
                'nombre' => $datos['nombre'],
                'nombre_completo' => $datos['nombre_completo'],
                'email' => $datos['email'] ?? null,
            ])->save();
        } catch (QueryException $exception) {
            if ($this->esConflictoUnico($exception)) {
                return $this->respuestaNombreOcupado($datos['nombre'], $datos['nombre_completo']);
            }
            throw $exception;
        }

        $this->audit->record($request, $actor, 'usuario.actualizado', $usuario, [
            'antes' => $antes,
            'despues' => $usuario->only(['nombre', 'nombre_completo', 'email']),
            'rol' => $usuario->rol,
        ]);

        return response()->json([
            'success' => true,
            'message' => $usuario->esProfesor() ? 'Perfil del profesor actualizado exitosamente.' : 'Perfil actualizado exitosamente.',
            'usuario' => $usuario->fresh()->load('colegio:id,nombre,codigo,activo'),
        ]);
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

        return response()->json(['success' => true, 'message' => 'Contraseña restablecida exitosamente.']);
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

        return response()->json([
            'success' => true,
            'message' => $usuario->esProfesor() ? 'Profesor eliminado exitosamente.' : 'Usuario eliminado exitosamente.',
        ]);
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

    private function nombreExiste(string $nombre, ?int $exceptoId = null): bool
    {
        return Usuario::query()
            ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre, 'UTF-8')])
            ->when($exceptoId, fn ($query) => $query->where('id', '<>', $exceptoId))
            ->exists();
    }

    public function suggestUsernameApi(Request $request): JsonResponse
    {
        $this->actor($request);
        $datos = $request->validate(['nombre_completo' => ['required', 'string', 'min:3', 'max:160']]);
        return response()->json(['suggested_username' => $this->sugerirNombreDesdeNombreCompleto($datos['nombre_completo'])]);
    }

    private function sugerirNombreDesdeNombreCompleto(string $nombreCompleto): string
    {
        $partes = preg_split('/\s+/u', $this->normalizarNombre($nombreCompleto), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if ($partes === []) return 'Usuario';
        $indiceApellido = count($partes) >= 3 ? count($partes) - 2 : min(1, count($partes) - 1);
        $inicial = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', Str::ascii($partes[0])) ?: 'U', 0, 1));
        $apellidoLimpio = preg_replace('/[^A-Za-z0-9]/', '', Str::ascii($partes[$indiceApellido])) ?: 'Usuario';
        $base = $inicial.ucfirst(strtolower($apellidoLimpio));
        $base = mb_substr($base, 0, 100, 'UTF-8');
        if (!$this->nombreExiste($base)) return $base;
        for ($numero = 2; $numero <= 9999; $numero++) {
            $sufijo = (string) $numero;
            $sugerencia = mb_substr($base, 0, 100 - strlen($sufijo), 'UTF-8').$sufijo;
            if (!$this->nombreExiste($sugerencia)) return $sugerencia;
        }
        return mb_substr($base, 0, 92, 'UTF-8').substr((string) hrtime(true), -8);
    }

    private function respuestaNombreOcupado(string $nombre, string $nombreCompleto): JsonResponse
    {
        $sugerencia = $this->sugerirNombreDesdeNombreCompleto($nombreCompleto);
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
            'nombre_completo.required' => 'El nombre completo es obligatorio.',
            'nombre_completo.min' => 'Ingresa el nombre completo de la persona.',
            'nombre_completo.max' => 'El nombre completo no puede superar los 160 caracteres.',
            'nombre.max' => 'El nombre de usuario no puede superar los 100 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio para profesores y administradores de colegio.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.unique' => 'Ya existe una cuenta registrada con este correo electrónico.',
            'email.max' => 'El correo no puede superar los 150 caracteres.',
            'rol.in' => 'El rol solicitado no está permitido.',
            'colegio_id.required' => 'Debes seleccionar un colegio.',
            'colegio_id.exists' => 'El colegio seleccionado no existe.',
            'clase_id.required' => 'Debes seleccionar una de tus clases.',
            'clase_id.exists' => 'La clase seleccionada no existe.',
            'profesor_id.exists' => 'El profesor seleccionado no existe.',
            'clave.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'nueva_clave.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ];
    }
}
