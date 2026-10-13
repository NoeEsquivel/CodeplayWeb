<?php

namespace App\Services;

use App\Models\TestSesion;

class InteractiveTestRules
{
    public const TIPOS_PREGUNTA = ['opcion_unica', 'seleccion_multiple', 'verdadero_falso', 'respuesta_directa'];

    public const ORIGENES_BANCO = ['sistema', 'alumno', 'profesor'];

    public const TIPOS_SESION = ['practica', 'amigos', 'clase'];

    public const MODALIDADES = ['practica', 'trivia', 'examen'];

    public const ESTADOS = ['configuracion', 'espera', 'activa', 'pausada', 'finalizada', 'cerrada'];

    public const MAX_PARTICIPANTES = 30;

    private const TRANSICIONES = [
        'configuracion' => ['espera', 'cerrada'],
        'espera' => ['configuracion', 'activa', 'cerrada'],
        'activa' => ['pausada', 'finalizada', 'cerrada'],
        'pausada' => ['activa', 'finalizada', 'cerrada'],
        'finalizada' => ['configuracion', 'espera', 'cerrada'],
        'cerrada' => [],
    ];

    public function puedeTransicionar(string $actual, string $nuevo): bool
    {
        return in_array($nuevo, self::TRANSICIONES[$actual] ?? [], true);
    }

    public function transicionar(TestSesion $sesion, string $nuevo): TestSesion
    {
        if (! $this->puedeTransicionar($sesion->estado, $nuevo)) {
            throw new \DomainException("No se puede cambiar una sesión de {$sesion->estado} a {$nuevo}.");
        }

        $sesion->estado = $nuevo;
        $sesion->version++;
        if ($nuevo === 'activa' && ! $sesion->iniciada_at) {
            $sesion->iniciada_at = now();
        }
        if ($nuevo === 'finalizada') {
            $sesion->finalizada_at = now();
        }
        if ($nuevo === 'cerrada') {
            $sesion->cerrada_at = now();
        }
        $sesion->save();

        return $sesion;
    }
}
