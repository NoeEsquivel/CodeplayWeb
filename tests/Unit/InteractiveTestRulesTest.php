<?php

namespace Tests\Unit;

use App\Models\TestBancoPregunta;
use App\Services\InteractiveTestRules;
use App\Services\LiveTestScoringService;
use App\Services\TestAnswerEvaluator;
use PHPUnit\Framework\TestCase;

class InteractiveTestRulesTest extends TestCase
{
    public function test_all_supported_question_types_are_evaluated(): void
    {
        $service = new TestAnswerEvaluator();
        $this->assertTrue($service->esCorrecta(new TestBancoPregunta(['tipo' => 'opcion_unica', 'respuestas_correctas' => ['b']]), 'B'));
        $this->assertTrue($service->esCorrecta(new TestBancoPregunta(['tipo' => 'seleccion_multiple', 'respuestas_correctas' => ['a', 'c']]), ['c', 'a']));
        $this->assertTrue($service->esCorrecta(new TestBancoPregunta(['tipo' => 'verdadero_falso', 'respuestas_correctas' => [true]]), 'sí'));
        $this->assertTrue($service->esCorrecta(new TestBancoPregunta(['tipo' => 'respuesta_directa', 'respuestas_correctas' => ['Programación', 'codigo']]), 'programacion'));
        $this->assertFalse($service->esCorrecta(new TestBancoPregunta(['tipo' => 'respuesta_directa', 'respuestas_correctas' => ['HTML']]), 'CSS'));
    }

    public function test_speed_ranking_distributes_from_one_hundred_to_a_fair_minimum(): void
    {
        $service = new LiveTestScoringService();
        $this->assertSame(100, $service->puntosPorOrden(true, 1, 2));
        $this->assertSame(50, $service->puntosPorOrden(true, 2, 2));
        $this->assertSame(100, $service->puntosPorOrden(true, 1, 10));
        $this->assertSame(10, $service->puntosPorOrden(true, 10, 10));
        $this->assertSame(0, $service->puntosPorOrden(false, 1, 10));
    }

    public function test_only_safe_session_state_transitions_are_allowed(): void
    {
        $rules = new InteractiveTestRules();
        $this->assertTrue($rules->puedeTransicionar('configuracion', 'espera'));
        $this->assertTrue($rules->puedeTransicionar('espera', 'activa'));
        $this->assertTrue($rules->puedeTransicionar('activa', 'finalizada'));
        $this->assertFalse($rules->puedeTransicionar('activa', 'configuracion'));
        $this->assertFalse($rules->puedeTransicionar('cerrada', 'activa'));
    }
}

