<?php

namespace App\Services;

use App\Models\TestBancoPregunta;
use Illuminate\Support\Str;

class TestAnswerEvaluator
{
    public function esCorrecta(TestBancoPregunta $pregunta, mixed $respuesta): bool
    {
        $correctas = $pregunta->respuestas_correctas ?? [];

        return match ($pregunta->tipo) {
            'opcion_unica' => count($correctas) === 1 && $this->normalizar($respuesta) === $this->normalizar($correctas[0]),
            'seleccion_multiple' => $this->normalizarLista($respuesta) === $this->normalizarLista($correctas),
            'verdadero_falso' => count($correctas) === 1 && $this->normalizarBooleano($respuesta) === $this->normalizarBooleano($correctas[0]),
            'respuesta_directa' => collect($correctas)->contains(fn ($correcta) => $this->normalizar($respuesta) === $this->normalizar($correcta)),
            default => false,
        };
    }

    private function normalizar(mixed $valor): string
    {
        $texto = Str::lower(Str::ascii(trim((string) $valor)));

        return preg_replace('/\s+/', ' ', $texto) ?? $texto;
    }

    private function normalizarLista(mixed $valores): array
    {
        if (! is_array($valores)) {
            return [];
        }
        $lista = array_values(array_unique(array_map(fn ($valor) => $this->normalizar($valor), $valores)));
        sort($lista);

        return $lista;
    }

    private function normalizarBooleano(mixed $valor): ?bool
    {
        if (is_bool($valor)) {
            return $valor;
        }

        return match ($this->normalizar($valor)) {
            '1', 'true', 'verdadero', 'si' => true,
            '0', 'false', 'falso', 'no' => false,
            default => null,
        };
    }
}
