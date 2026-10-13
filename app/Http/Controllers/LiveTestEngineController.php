<?php

namespace App\Http\Controllers;

use App\Models\TestBanco;
use App\Models\TestSesion;
use App\Models\Usuario;
use App\Services\LiveTestEngineService;
use App\Services\LiveTestSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LiveTestEngineController extends Controller
{
    public function create(Request $request, LiveTestSessionService $sesiones): JsonResponse
    {
        $datos = $request->validate([
            'tipo' => ['required', 'in:amigos,clase'],
            'modalidad' => ['nullable', 'in:trivia'],
            'clase_id' => ['nullable', 'integer'],
            'banco_ids' => ['required', 'array', 'min:1', 'max:20'],
            'banco_ids.*' => ['integer', 'distinct'],
            'palabra_clave' => ['nullable', 'string', 'min:3', 'max:60'],
            'tiempo_pregunta' => ['nullable', 'integer', 'min:5', 'max:300'],
            'segundos_resultado' => ['nullable', 'integer', 'min:2', 'max:20'],
            'puntaje_objetivo' => ['nullable', 'integer', 'min:50', 'max:100000'],
            'max_participantes' => ['nullable', 'integer', 'min:1', 'max:30'],
        ]);

        try {
            $bancos = TestBanco::whereIn('id', $datos['banco_ids'])->get();
            if ($bancos->count() !== count($datos['banco_ids'])) {
                throw new \DomainException('Uno de los bancos seleccionados no existe.');
            }
            $sesion = $sesiones->crear($this->usuario($request), [
                ...$datos,
                'modalidad' => 'trivia',
                'configuracion' => ['segundos_resultado' => (int) ($datos['segundos_resultado'] ?? 5)],
            ], $bancos->all());

            return response()->json([
                'success' => true,
                'sesion_id' => $sesion->id,
                'codigo_sala' => $sesion->codigo_sala,
                'estado' => $sesion->estado,
            ], 201);
        } catch (\DomainException|\InvalidArgumentException $e) {
            return $this->error($e);
        }
    }

    public function open(Request $request, TestSesion $sesion, LiveTestEngineService $motor): JsonResponse
    {
        return $this->accion(fn () => ['sesion' => $motor->abrirSala($this->usuario($request), $sesion)]);
    }

    public function join(Request $request, LiveTestEngineService $motor): JsonResponse
    {
        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:60'],
            'palabra_clave' => ['nullable', 'string', 'max:60'],
        ]);

        return $this->accion(fn () => $motor->unir(
            $this->usuario($request),
            $datos['codigo'],
            $datos['palabra_clave'] ?? null,
        ));
    }

    public function reconnect(Request $request, TestSesion $sesion, LiveTestEngineService $motor): JsonResponse
    {
        $token = $request->validate(['reconexion_token' => ['required', 'string', 'size:64']])['reconexion_token'];

        return $this->accion(fn () => $motor->reconectar($this->usuario($request), $sesion, $token));
    }

    public function start(Request $request, TestSesion $sesion, LiveTestEngineService $motor): JsonResponse
    {
        return $this->accion(fn () => ['sesion' => $motor->iniciar($this->usuario($request), $sesion)]);
    }

    public function state(Request $request, TestSesion $sesion, LiveTestEngineService $motor): JsonResponse
    {
        return $this->accion(fn () => $motor->estado($this->usuario($request), $sesion));
    }

    public function answer(Request $request, TestSesion $sesion, LiveTestEngineService $motor): JsonResponse
    {
        $request->validate(['respuesta' => ['present']]);
        $respuesta = $request->input('respuesta');
        $valores = is_array($respuesta) ? $respuesta : [$respuesta];
        if (count($valores) > 20 || collect($valores)->contains(
            fn ($valor) => ! is_scalar($valor) || mb_strlen((string) $valor) > 1000
        )) {
            return response()->json(['success' => false, 'message' => 'La respuesta enviada no es válida.'], 422);
        }

        return $this->accion(function () use ($request, $sesion, $motor, $respuesta) {
            $motor->responder($this->usuario($request), $sesion, $respuesta);

            return $motor->estado($this->usuario($request), $sesion->fresh());
        });
    }

    public function close(Request $request, TestSesion $sesion, LiveTestEngineService $motor): JsonResponse
    {
        return $this->accion(fn () => ['sesion' => $motor->cerrar($this->usuario($request), $sesion)]);
    }

    private function accion(callable $accion): JsonResponse
    {
        try {
            return response()->json(['success' => true, ...$accion()]);
        } catch (\DomainException|\InvalidArgumentException $e) {
            return $this->error($e);
        }
    }

    private function error(\Throwable $e): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
    }

    private function usuario(Request $request): Usuario
    {
        $usuario = $request->attributes->get('usuario_actual');

        return $usuario instanceof Usuario ? $usuario : Usuario::findOrFail((int) session('usuario_id'));
    }
}
