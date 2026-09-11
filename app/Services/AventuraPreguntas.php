<?php

namespace App\Services;

use App\Models\PreguntaTest;

class AventuraPreguntas
{
    public function elegir(int $escenario, array $usadas = []): ?PreguntaTest
    {
        // The existing bank is classified by teaching module, not a numeric difficulty.
        $grupos = [range(1,4), range(1,4), range(1,4), range(1,5)];
        $prefijos = ['html','css','js','mundo'];
        $hasta = min(3, intdiv(max(1,$escenario)-1,10));
        for ($grupo=$hasta; $grupo>=0; $grupo--) {
            $modulos = array_map(fn($n)=>$prefijos[$grupo].$n,$grupos[$grupo]);
            $query = PreguntaTest::whereHas('test',fn($q)=>$q->whereIn('modulo',$modulos))->whereIn('respuesta_correcta',['a','b','c','d']);
            $pregunta = (clone $query)->whereNotIn('id',$usadas)->inRandomOrder()->first() ?? $query->inRandomOrder()->first();
            if ($pregunta) return $pregunta;
        }
        return null;
    }

    public function publica(?int $id): ?array
    {
        return $id ? PreguntaTest::find($id)?->only(['pregunta','opcion_a','opcion_b','opcion_c','opcion_d']) : null;
    }
}
