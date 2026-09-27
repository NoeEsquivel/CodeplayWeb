<?php

namespace App\Services;

use App\Models\TestBanco;
use App\Models\TestSesion;
use App\Models\TestSesionParticipante;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BankPracticeService
{
    public function crear(Usuario $usuario, TestBanco $banco): TestSesion
    {
        $esPropietario = (int) $banco->creador_usuario_id === (int) $usuario->id
            && (int) $banco->colegio_id === (int) $usuario->colegio_id;
        if (!$esPropietario) app(TestBankService::class)->validarBancoBiblioteca($usuario, $banco);

        $preguntas = $banco->preguntas()->where('activa', true)->get()->shuffle()->values();
        if ($preguntas->isEmpty()) throw new \DomainException('Este banco todavía no tiene preguntas disponibles.');

        return DB::transaction(function () use ($usuario, $banco, $preguntas) {
            $sesion = app(LiveTestSessionService::class)->crear($usuario, [
                'tipo' => 'practica', 'modalidad' => 'practica', 'max_participantes' => 1,
                'configuracion' => [
                    'origen' => 'banco_personal', 'banco_id' => $banco->id, 'nombre' => $banco->nombre,
                    'cantidad' => $preguntas->count(), 'con_tiempo' => true, 'otorga_puntos_codeplay' => false,
                ],
            ], [$banco]);

            TestSesionParticipante::create([
                'sesion_id' => $sesion->id, 'usuario_id' => $usuario->id, 'nombre_visible' => $usuario->nombre,
                'reconexion_token_hash' => hash('sha256', Str::random(64)), 'estado' => 'conectado',
                'es_anfitrion' => true, 'puntos_test' => 0, 'unido_at' => now(), 'ultima_actividad_at' => now(),
            ]);
            foreach ($preguntas as $indice => $pregunta) {
                $sesion->preguntas()->create([
                    'pregunta_id' => $pregunta->id, 'ronda' => 1, 'orden' => $indice + 1, 'estado' => 'pendiente',
                ]);
            }
            $reglas = app(InteractiveTestRules::class);
            $reglas->transicionar($sesion, 'espera');
            $reglas->transicionar($sesion, 'activa');
            return $sesion;
        });
    }
}

