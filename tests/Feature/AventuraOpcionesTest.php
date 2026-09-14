<?php

namespace Tests\Feature;

use App\Services\AventuraPreguntas;
use Tests\TestCase;

class AventuraOpcionesTest extends TestCase
{
    public function test_options_are_stable_and_every_answer_can_occupy_every_position(): void
    {
        config(['app.key' => 'option-order-test-key']);
        foreach (['combate', 'evento', 'gremio'] as $tipo) {
            $positions = ['a'=>[], 'b'=>[], 'c'=>[], 'd'=>[]];
            for ($encounter=1; $encounter<=96; $encounter++) {
                $context = [$tipo, 7, $encounter, 3, 1];
                $order = AventuraPreguntas::ordenOpciones($context);
                self::assertSame($order, AventuraPreguntas::ordenOpciones($context));
                $sorted=$order; sort($sorted);
                self::assertSame(['a','b','c','d'], $sorted);
                foreach ($order as $position=>$answer) $positions[$answer][$position]=true;
            }
            foreach ($positions as $seen) self::assertCount(4, $seen);
        }
    }
}
