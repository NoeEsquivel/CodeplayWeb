<?php

namespace App\Http\Controllers;

use App\Models\TestBanco;
use App\Models\TestSesion;
use App\Models\Usuario;
use App\Services\LiveTestEngineService;
use App\Services\LiveTestSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class FriendLiveTestController extends Controller
{
    public function index(): View
    {
        $usuario = $this->usuario();
        $bancos = $this->bancosDisponibles($usuario);
        $sesiones = TestSesion::where('tipo', 'amigos')
            ->where('colegio_id', $usuario->colegio_id)
            ->whereIn('estado', ['configuracion', 'espera', 'activa', 'pausada'])
            ->where(function ($query) use ($usuario) {
                $query->where('anfitrion_usuario_id', $usuario->id)
                    ->orWhereHas('participantes', fn ($q) => $q->where('usuario_id', $usuario->id));
            })->latest()->get();

        return view('alumno.tests-amigos', compact('bancos', 'sesiones'));
    }

    public function store(Request $request, LiveTestSessionService $sesiones, LiveTestEngineService $motor): RedirectResponse
    {
        $datos = $this->validarConfiguracion($request);
        $usuario = $this->usuario();
        try {
            $bancos = $this->resolverBancos($usuario, $datos['banco_ids']);
            $sesion = $sesiones->crear($usuario, [
                'tipo' => 'amigos', 'modalidad' => 'trivia',
                'palabra_clave' => $datos['palabra_clave'],
                'tiempo_pregunta' => $datos['tiempo_pregunta'],
                'puntaje_objetivo' => $datos['puntaje_objetivo'],
                'max_participantes' => 30,
                'configuracion' => [
                    'segundos_resultado' => 5,
                    'orden_aleatorio' => $request->boolean('orden_aleatorio'),
                ],
            ], $bancos->all());
            $motor->abrirSala($usuario, $sesion);
            $union = $motor->unir($usuario, $sesion->codigo_sala, $datos['palabra_clave']);
            $request->session()->put("live_test_tokens.{$sesion->id}", $union['reconexion_token']);

            return redirect()->route('alumno.tests.amigos.room', $sesion)
                ->with('success', 'Mesa creada. Comparte la palabra clave con tus amigos.');
        } catch (\DomainException|\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function join(Request $request, LiveTestEngineService $motor): RedirectResponse
    {
        $acceso = trim((string) $request->validate(['acceso' => ['required', 'string', 'min:3', 'max:60']])['acceso']);
        try {
            $union = $motor->unir($this->usuario(), $acceso, null, 'amigos');
            $sesion = $union['sesion'];
            $request->session()->put("live_test_tokens.{$sesion->id}", $union['reconexion_token']);

            return redirect()->route('alumno.tests.amigos.room', $sesion);
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function room(TestSesion $sesion, LiveTestEngineService $motor): View
    {
        abort_unless($sesion->tipo === 'amigos', 404);
        try {
            $estado = $motor->estado($this->usuario(), $sesion);
        } catch (\DomainException) {
            abort(404);
        }
        $token = session("live_test_tokens.{$sesion->id}");

        return view('alumno.tests-amigos-sala', compact('sesion', 'estado', 'token'));
    }

    public function start(TestSesion $sesion, LiveTestEngineService $motor): RedirectResponse
    {
        return $this->accion($sesion, fn () => $motor->iniciar($this->usuario(), $sesion), 'La trivia comenzó.');
    }

    public function pause(TestSesion $sesion, LiveTestEngineService $motor): RedirectResponse
    {
        return $this->accion($sesion, fn () => $motor->pausar($this->usuario(), $sesion), 'Partida pausada.');
    }

    public function resume(TestSesion $sesion, LiveTestEngineService $motor): RedirectResponse
    {
        return $this->accion($sesion, fn () => $motor->reanudar($this->usuario(), $sesion), 'Partida reanudada.');
    }

    public function finish(TestSesion $sesion, LiveTestEngineService $motor): RedirectResponse
    {
        return $this->accion($sesion, fn () => $motor->cerrar($this->usuario(), $sesion), 'Partida terminada.');
    }

    public function repeat(TestSesion $sesion, LiveTestEngineService $motor): RedirectResponse
    {
        return $this->accion($sesion, fn () => $motor->repetir($this->usuario(), $sesion), 'La mesa volvió a la sala de espera.');
    }

    public function configure(TestSesion $sesion, LiveTestEngineService $motor): View|RedirectResponse
    {
        try {
            $this->autorizarAnfitrion($sesion);
            if ($sesion->estado !== 'configuracion') {
                $motor->repetir($this->usuario(), $sesion, true);
            }
            $sesion = $sesion->fresh();
            $bancos = $this->bancosDisponibles($this->usuario());
            $seleccionados = $sesion->bancos()->pluck('test_bancos.id')->map(fn ($id) => (int) $id)->all();

            return view('alumno.tests-amigos-configurar', compact('sesion', 'bancos', 'seleccionados'));
        } catch (\DomainException $e) {
            return redirect()->route('alumno.tests.amigos.room', $sesion)->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, TestSesion $sesion, LiveTestEngineService $motor): RedirectResponse
    {
        $datos = $this->validarConfiguracion($request);
        try {
            $bancos = $this->resolverBancos($this->usuario(), $datos['banco_ids']);
            $motor->reconfigurar($this->usuario(), $sesion, [
                ...$datos,
                'orden_aleatorio' => $request->boolean('orden_aleatorio'),
            ], $bancos->all());

            return redirect()->route('alumno.tests.amigos.room', $sesion)
                ->with('success', 'Configuración guardada. Todos continúan en la mesa de espera.');
        } catch (\DomainException|\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    private function bancosDisponibles(Usuario $usuario): Collection
    {
        return TestBanco::query()->where('activo', true)->where('estado', 'publicado')
            ->where(function ($query) use ($usuario) {
                $query->where('origen', 'sistema')
                    ->orWhere(fn ($q) => $q->where('colegio_id', $usuario->colegio_id)->where('creador_usuario_id', $usuario->id))
                    ->orWhereHas('usuariosBiblioteca', fn ($q) => $q->where('usuarios.id', $usuario->id));
            })
            ->withCount(['preguntas' => fn ($q) => $q->where('activa', true)])
            ->having('preguntas_count', '>', 0)
            ->orderBy('origen')->orderBy('nombre')->get();
    }

    private function resolverBancos(Usuario $usuario, array $ids): Collection
    {
        $disponibles = $this->bancosDisponibles($usuario)->keyBy('id');
        $seleccion = collect($ids)->map(fn ($id) => $disponibles->get((int) $id))->filter()->values();
        if ($seleccion->count() !== count(array_unique(array_map('intval', $ids)))) {
            throw new \DomainException('Uno de los bancos seleccionados no está disponible.');
        }

        return $seleccion;
    }

    private function validarConfiguracion(Request $request): array
    {
        return $request->validate([
            'banco_ids' => ['required', 'array', 'min:1', 'max:20'],
            'banco_ids.*' => ['integer', 'distinct'],
            'palabra_clave' => ['required', 'string', 'min:3', 'max:60'],
            'tiempo_pregunta' => ['required', 'integer', 'min:5', 'max:120'],
            'puntaje_objetivo' => ['required', 'integer', 'min:50', 'max:100000'],
            'orden_aleatorio' => ['nullable', 'boolean'],
        ]);
    }

    private function accion(TestSesion $sesion, callable $accion, string $mensaje): RedirectResponse
    {
        abort_unless($sesion->tipo === 'amigos', 404);
        try {
            $accion();

            return redirect()->route('alumno.tests.amigos.room', $sesion)->with('success', $mensaje);
        } catch (\DomainException $e) {
            return redirect()->route('alumno.tests.amigos.room', $sesion)->with('error', $e->getMessage());
        }
    }

    private function usuario(): Usuario
    {
        return Usuario::findOrFail((int) session('usuario_id'));
    }

    private function autorizarAnfitrion(TestSesion $sesion): void
    {
        $usuario = $this->usuario();
        if ($sesion->tipo !== 'amigos'
            || (int) $sesion->colegio_id !== (int) $usuario->colegio_id
            || (int) $sesion->anfitrion_usuario_id !== (int) $usuario->id) {
            throw new \DomainException('Solo el anfitrión puede configurar esta mesa.');
        }
    }
}
