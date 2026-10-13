<?php

namespace App\Services;

class LiveTestScoringService
{
    public function puntosPorOrden(bool $correcta, int $posicion, int $participantes): int
    {
        if (! $correcta || $participantes < 1) {
            return 0;
        }
        $posicion = max(1, min($posicion, $participantes));

        return (int) round(100 * (($participantes - $posicion + 1) / $participantes));
    }
}
